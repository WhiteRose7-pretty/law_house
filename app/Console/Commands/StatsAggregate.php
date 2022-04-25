<?php

namespace App\Console\Commands;

use App\User;
use App\Question;
use App\UserQuestion;
use App\UserQuestionAnswer;
use App\UserStatDailyAggregate;
use App\UserStatDailyAggregateSetSummary;
use App\UserStatDailyAggregateSetSummary1;
use App\UserStatWeeklyAggregateSetSummary;
use App\UserStatWeeklyAggregateSetSummary1;
use App\UserStatMonthlyAggregateSetSummary;
use App\UserStatMonthlyAggregateSetSummary1;
use App\UserStatDailyAggregateTotalSummary;
use App\UserStatDailyAggregateTotalSummary1;
use App\UserStatWeeklyAggregateTotalSummary;
use App\UserStatWeeklyAggregateTotalSummary1;
use App\UserStatMonthlyAggregateTotalSummary;
use App\UserStatMonthlyAggregateTotalSummary1;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StatsAggregate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iusvitae:stats-aggregate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For Each User - Go Through Answers and aggregate statistics';

    protected $dim = 25*60*60;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $u_skip = 0;
        $u_limit = 10;
        // - 1 * ta data powinna być WCZORAJ
        $u_date = date('Y-m-d',time()-$this->dim);

        $this->line('------------------------------------------------------------------------------');
        $this->info('[Processing batch]');
        $this->line("[UNTIL = $u_date]");
        $this->line('---------');

        do {
            $users = $this->getUsers($u_skip, $u_limit,$u_date);
            if (empty($users)) {
                continue;
            }
            foreach($users as $uid => $u) {
                if (empty($u['last_answer_date']) || (!empty($u['last_entry_date'] && ($u['last_answer_date'] <= $u['last_entry_date']) ) ) ) {
                    $this->line('Skipping: '.$u['email'].'; -entry: '.$u['last_entry_date'].'; -answer: '.$u['last_answer_date']);
                    continue;
                }
                $this->line('Processing: '.$u['email'].'; -entry: '.$u['last_entry_date'].'; -answer: '.$u['last_answer_date']);
                DB::beginTransaction();
                $this->processUser($uid,$u);
                DB::commit();
            }
            $u_skip+=$u_limit;
        } while(count($users) < $u_limit == false);

        $this->line('--------------------------');
        $this->info('ALL Done!');
        $this->line('--------------------------');

        $this->aggregate_data('App\UserStatDailyAggregateTotalSummary', 'App\UserStatDailyAggregateTotalSummary1', 1);
        $this->aggregate_data('App\UserStatDailyAggregateSetSummary', 'App\UserStatDailyAggregateSetSummary1', 0);

        $this->aggregate_data('App\UserStatMonthlyAggregateTotalSummary', 'App\UserStatMonthlyAggregateTotalSummary1', 1);
        $this->aggregate_data('App\UserStatMonthlyAggregateSetSummary', 'App\UserStatMonthlyAggregateSetSummary1', 0);

        $this->aggregate_data('App\UserStatWeeklyAggregateTotalSummary', 'App\UserStatWeeklyAggregateTotalSummary1', 1);
        $this->aggregate_data('App\UserStatWeeklyAggregateSetSummary', 'App\UserStatWeeklyAggregateSetSummary1', 0);

    }

    protected function aggregate_data($modal, $modal1, $total=1)
    {
        $modal1::truncate();
        $query = $modal::all();
        DB::beginTransaction();
        foreach($query as $q){
            $q1 = new $modal1();
            $q1->user_id = $q->user_id;
            $q1->date = $q->date;
            if ($total == 0){
                $q1->questions_set_id = $q->questions_set_id;
            }
            $q1->new = $q->new;
            $q1->repeat = $q->repeat;
            $q1->answers = $q->answers;
            $q1->correct = $q->correct;
            $q1->incorrect = $q->incorrect;
            $q1->all_answers = $q->all_answers;
            $q1->all_correct = $q->all_correct;
            $q1->all_incorrect = $q->all_incorrect;
            $q1->all_repeat = $q->all_repeat;
            $q1->correct_0 = $q->correct_0;
            $q1->correct_1 = $q->correct_1;
            $q1->correct_2 = $q->correct_2;
            $q1->correct_3 = $q->correct_3;
            $q1->correct_4 = $q->correct_4;
            $q1->correct_5 = $q->correct_5;
            $q1->correct_m = $q->correct_m;
            $q1->save();
        }
        DB::commit();
    }

    protected function processUser($uid, $user) {
        $ids = $this->getQuestionIds($uid, $user);
        $count = 0;
        $timer = microtime(true);
        foreach($ids as $qid) {
            $date = $this->getStartDate($user);
            $previous = null;
            $this->line('--------------processing question:'.$qid);
            do {
                if (empty($previous)) {
                    $previous = $this->getPrevious($uid,$qid);
                }
                $current = $this->processQuestion($uid,$qid,$date);
                if (empty($previous) && empty($current['answers'])) {
                    $date = date('Y-m-d',strtotime($date) + $this->dim);
                    continue;
                }
                $count++;


                $previous = $this->setCurrent($uid,$qid,$previous,$current, $date);
                $date = date('Y-m-d',strtotime($date) + $this->dim);
            } while($date <= $user['last_answer_date']);
        }
        $timer = microtime(true) - $timer;
        $this->info("Entries [$count], execution time [$timer]");
    }

    protected function setCurrent($uid,$qid,$previous,$current) {
        if (empty($previous)) {
            $current['all_answers'] = $current['answers'];
            $current['all_correct'] = $current['correct'];
            $current['all_incorrect'] = $current['incorrect'];
            $current['all_repeat'] = $current['repeat'];
            $current['new'] = true;
            $usda = new UserStatDailyAggregate($current);
            $usda->save();
            // print_r($previous);
            // print_r($current);
            return $current;
        }
        $current['change'] = !empty($current['answers']);
        $current['questions_set_id'] = $previous['questions_set_id'];
        $current['all_answers'] = $previous['all_answers'] + $current['answers'];
        $current['all_correct'] = $previous['all_correct'] + $current['correct'];
        $current['all_incorrect'] = $previous['all_incorrect'] + $current['incorrect'];
        $current['all_repeat'] = $previous['all_repeat'] + $current['repeat'];
        $current['correct_in_row'] = $current['incorrect'] ? $current['correct_in_row'] : $current['correct_in_row'] + $previous['correct_in_row'];
        $usda = new UserStatDailyAggregate($current);
        $usda->save();
        // print_r($previous);
        // print_r($current);
        return $current;
    }

    protected function getPrevious($uid,$qid) {
        $tmp = UserStatDailyAggregate::where('user_id',$uid)->where('question_id',$qid)->orderBy('id','desc')->first();
        if (empty($tmp)) {
            return $tmp;
        }
        return [
            'user_id' => $uid,
            'date' => $tmp->date,
            'question_id' => $tmp->question_id,
            'questions_set_id' => $tmp->questions_set_id,
            'correct_in_row' => $tmp->correct_in_row,
            'all_answers' => $tmp->all_answers,
            'all_correct' => $tmp->all_correct,
            'all_incorrect' => $tmp->all_incorrect,
            'all_repeat' => $tmp->all_repeat,
        ];
    }

    protected function processQuestion($uid, $qid, $date) {
        $tmp = UserQuestionAnswer::with('question')->where('user_id',$uid)->where('question_id',$qid)->whereRaw('date(created_at)=?',$date)->orderBy('id')->get();
        $stat = [
            'user_id' => $uid,
            'question_id' => $qid,
            'date' => $date,
            'answers' => 0,
            'repeat' => 0,
            'correct_in_row' => 0,
            'correct' => 0,
            'incorrect' => 0,
        ];
        foreach($tmp as $a) {
            $stat['answers']++;
            $stat['questions_set_id'] = $a->question->questions_set_id;
            if ($a->is_repeat) {
                $stat['repeat']++;
            }
            if ($a->correct) {
                $stat['correct']++;
                $stat['correct_in_row']++;
                continue;
            }
            $stat['correct_in_row'] = 0;
            $stat['incorrect'] ++;
        }
        return $stat;
    }

    protected function getQuestionIds($uid, $user) {

        $tmp = UserQuestionAnswer::select(DB::raw('distinct question_id'))->where('user_id',$uid)->whereRaw('date(`created_at`) >= ?',$this->getStartDate($user))->whereRaw('date(`created_at`) <= ?',$user['last_answer_date'])->get();
        $ids = [];
        foreach($tmp as $a) {
          $ids[]=$a->question_id;
        }
        return $ids;
    }

    protected function getStartDate($user) {
        if (!empty($user['last_entry_date'])) {
            return date('Y-m-d', strtotime($user['last_entry_date']) + $this->dim);
        }
        return $user['first_answer_date'];
    }

    protected function getUsers($skip=0,$take=10,$date) {
        $tmp = User::select('id','email')->orderBy('id')->skip($skip)->take($take)->get();
        $users = [];
        $ids = [];
        foreach($tmp as $u) {
            $users[$u->id] = ['email'=>$u->email,'last_entry_date'=>null,'last_answer_date'=>null];
            $ids[] = $u->id;
        }
        $tmp = UserStatDailyAggregate::select('user_id',DB::raw('max(`date`) as `last_entry_date`'))->whereIn('user_id',$ids)->groupBy('user_id')->get();
        foreach($tmp as $r) {
            $users[$r->user_id]['last_entry_date'] = $r->last_entry_date;
        }
        $tmp = UserQuestionAnswer::select('user_id',DB::raw('date(min(`created_at`)) as `first_answer_date`'),DB::raw('date(max(`created_at`)) as `last_answer_date`'))->whereIn('user_id',$ids)->whereRaw('date(created_at)<=?',$date)->groupBy('user_id')->get();
        foreach($tmp as $r) {
            $users[$r->user_id]['last_answer_date'] = $r->last_answer_date;
            $users[$r->user_id]['first_answer_date'] = $r->first_answer_date;
        }
        return $users;
    }
}
