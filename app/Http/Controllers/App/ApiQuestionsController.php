<?php

namespace App\Http\Controllers\App;

use App\ApiHash;
use App\UserPackageQuestionsSet;
use App\QuestionsSet;
use App\UserQuestion;
use App\UserStatQuestionAnswerDailySummary;
use App\UserQuestionCalendar;
use App\UserQuestionRepeat;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiQuestionsController extends Controller
{

    public function available(Request $request)
    {
        Log::debug('call1---->'.Carbon::now()->toDateTimeString());
        $h = ApiHash::find($request->input('hash_id'));
        $ids = UserPackageQuestionsSet::available_ids($h->user_id);
        Log::debug('call2---->'.Carbon::now()->toDateTimeString());
        $id = $request->input('set_id');
        $q = $request->input('query');
        $l = $request->input('limit');
        $p = $request->input('page');
        $f_edu = $request->input('filter_edu');
        $f_order = $request->input('filter_order');
        if (!empty($id) && !in_array($id,$ids)) {
            return response()->json([]);
        }
        $f = UserQuestion::with('options')->where('user_id',$h->user_id);
        if (!empty($id)) {
            $f = $f->where('questions_set_id',$id);
        }
        if (!empty($q)) {
            $f = $f->where('question','like','%'.$q.'%');
        }

        $f_edu = (string)$f_edu;
        switch($f_edu) {
            case 'new':
              $f->whereNull('last_answer_at');
              break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
              $f->where('correct_in_row',$f_edu);
              break;
            case 'plus':
              $f->where('correct_in_row','>',5);
              break;
            case 'skip':
              $f->where('skip',1);
              break;
        }

        $f_order = (string)$f_order;
        switch($f_order) {
            case 'id':
                $f->orderBy('id','asc');
                break;
            case 'last-answered':
                $f->whereNotNull('last_answer_at');
                $f->orderBy('last_answer_at','desc');
                break;
            case 'last-correct':
                $f->whereNotNull('last_correct_at');
                $f->orderBy('last_correct_at','desc');
                break;
            case 'last-incorrect':
                $f->whereNotNull('last_incorrect_at');
                $f->orderBy('last_incorrect_at','desc');
                break;
            case 'last-repeat':
                $f->whereNotNull('last_repeat_at');
                $f->orderBy('last_repeat_at','desc');
                break;
            case 'next-repeat':
                $f->whereNotNull('next_repeat_at');
                $f->orderBy('next_repeat_at','desc');
                break;
            case 'newest':
                $f->orderBy('id','desc');
                break;
            case 'updated':
                $f->orderBy('updated_at','desc');
                break;
        }

        Log::debug('call3---->'.Carbon::now()->toDateTimeString());
        $count = $f->count();
        Log::debug('call4---->'.Carbon::now()->toDateTimeString());
        $pages = ceil($count/$l);
        $page = $p > $pages ? $pages : $p;
        $page = empty($page) ? 1 : $page;
        $skip = ($page-1)*$l;
        Log::debug('call5---->'.Carbon::now()->toDateTimeString());
        $results = $f->skip($skip)->take($l)->get();
        Log::debug('call6---->'.Carbon::now()->toDateTimeString());
        return response()->json([
            'page' => $page,
            'pages' => $pages,
            'count' => $count,
            'results' => $results,
        ]);
    }

    public function calendar(Request $request)
    {

        $spread = 5;
        $h = ApiHash::find($request->input('hash_id'));
        $tmp = UserStatQuestionAnswerDailySummary::where('user_id',$h->user_id)->where('answered_at','>=',DB::raw('date(now() - interval '.$spread.' day)'))->get();
        $count = UserQuestionRepeat::select(DB::raw('count(*) as c'))->where('user_id',$h->user_id)->whereRaw('date(next_repeat_at) = ?',date('Y-m-d'))->first()->c;
        $l1 = [];
        foreach($tmp as $s) {
          $l1[$s->answered_at]=[
              'type' => 'summary',
              'date' => $s->answered_at,
              'day' => $this->dayText($s->answered_at),
              'correct' => $s->correct,
              'total' => $s->total,
          ];
        }
        $tmp = UserQuestionCalendar::select('planned_at',DB::raw('count(*) as number'))->where('user_id',$h->user_id)->where('planned_at','<=',DB::raw('date(now() + interval '.$spread.' day)'))->groupBy('planned_at')->get();
        $l2 = [];
        foreach($tmp as $c) {
          $l2[$c->planned_at]=[
              'type' => 'plan',
              'date' => $c->planned_at,
              'day' => $this->dayText($c->planned_at),
              'number' => $c->number,
          ];
        }
        $today = date('Y-m-d');
        $start = date('Y-m-d',time()-$spread*24*60*60);
        $end = date('Y-m-d',time()+$spread*24*60*60);
        $postpone = empty($l2[$today]) ? false : ($l2[$today]['number'] > $count ? true : false);
        $list = [];
        for($i=0;$i<11;$i++) {
            $d = date('Y-m-d',strtotime($start)+$i*24*60*60);
            if ($d>$end) {
                break;
            }
            if ($d < $today) {
                if (!empty($l1[$d])) {
                    $list[] = $l1[$d];
                    continue;
                }
                $list[] = [
                    'type' => 'summary',
                    'date' => $d,
                    'day' => $this->dayText($d),
                    'total' => 0,
                    'correct' => 0,
                ];
                continue;
            }
            if (!empty($l2[$d])) {
                $list[] = $l2[$d];
                continue;
            } elseif(!empty($l1[$d])) {
                $list[] = $l1[$d];
                continue;
            }
            $list[] = [
                'type' => 'plan',
                'date' => $d,
                'day' => $this->dayText($d),
                'number' => 0,
            ];
        }
        return response()->json([
            'today' => $today,
            'postpone' => $postpone,
            'count' => $count,
            'list' => $list,
        ]);
    }

    public function postpone(Request $request) {
        $h = ApiHash::find($request->input('hash_id'));
        $d = $request->input('days');
        $d = intval($d);
        UserQuestionRepeat::where('user_id',$h->user_id)->whereNotNull('next_repeat_at')->update(['next_repeat_at'=>DB::raw('next_repeat_at + interval '.$d.' day')]);
        return response()->json();
    }

    protected function dayText($date) {
        if ($date==date('Y-m-d')) {
            return 'Dzisiaj';
        }
        if ($date==date('Y-m-d',time()-24*60*60)) {
            return 'Wczoraj';
        }
        if ($date==date('Y-m-d',time()+24*60*60)) {
            return 'Jutro';
        }
        $d = date('N',strtotime($date));
        $ds = [1=>'Pon.',2=>'Wt.',3=>'Śr.',4=>'Czw.',5=>'Pt.',6=>'Sob.',7=>'Nie.'];
        return $ds[$d];
    }

    public function skip(Request $request) {
        $h = ApiHash::find($request->input('hash_id'));
        $q = $request->input('question_id');
        $f = $request->input('toggle');
        $qr = UserQuestionRepeat::where('user_id',$h->user_id)->where('question_id',$q)->first();
        if (empty($qr)) {
            return response()->json(['message'=>'Nie znaleziono wpisu w systemie powtórek'], 404);
        }
        if (empty($f) && empty($qr->correct_in_row)) {
            return response()->json(['message'=>'Nie można zmodyfikować tego wpisu. Ostatnia odpowiedź nie jest poprawną.'], 409);
        }
        $qr->skip = $f;
        if (empty($f)&&!empty($qr->next_repeat_at)) {
            if (strtotime($qr->next_repeat_at)<time()) {
                $qr->next_repeat_at=date('Y-m-d H:i:s');
            }
        }
        $qr->save();
        return response()->json();
    }

}
