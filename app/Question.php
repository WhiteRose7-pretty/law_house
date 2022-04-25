<?php

namespace App;

use App\UserQuestionRepeat;
use Illuminate\Database\Eloquent\Model;
use function foo\func;

class Question extends Model
{
    function options()
    {
        return $this->hasMany('App\QuestionOption');
    }

    function laws()
    {
        return $this->hasMany('App\QuestionLaw');
    }

    function user_question_answers()
    {
        return $this->hasMany('App\UserQuestionAnswer');
    }

    function users_game_participants_answers()
    {
        return $this->hasMany('App\UserGameParticipantAnswer');
    }


    function users_game_questions()
    {
        return $this->hasMany('App\UserGameQuestion');
    }

    function users_tests_questions()
    {
        return $this->hasMany('App\UserTestQuestion');
    }

    function users_questions_repeats()
    {
        return $this->hasMany('App\UserQuestionRepeat');
    }

    function users_stats_daily_aggregates()
    {
        return $this->hasMany('App\UserStatDailyAggregate');
    }


    function questions_set()
    {
        return $this->belongsTo('App\QuestionsSet', 'questions_set_id');
    }

    static protected function processList($user_id, $ids, $shuffle = false, $answers = [], $filter = null, $repeat = false)
    {
        $uqrs = UserQuestionRepeat::listById($user_id, $ids);
        if (!$repeat) {
            $tmp = self::with(['options' => function ($q) {
                $q->orderByRaw('RAND()');
            }])->whereIn('id', $ids)->get();
        } else {
            $tmp = self::with(['options' => function ($q) {
                $q->orderByRaw('RAND()');
            }])->whereIn('id', $ids)
                ->whereHas('questions_sets', function ($q) {
                    $q->where('repeatable', 1);
                })->get();
        }

        $list = [];
        foreach ($tmp as $q) {
            $r = [
                'id' => $q->id,
                'question' => $q->question,
                'help_text' => $q->help_text,
                'legal_basis_text' => $q->legal_basis_text,
                'legal_basis_generated' => $q->legal_basis_generated,
                'deleted' => $q->deleted,
            ];
            if (!empty($uqrs[$q->id])) {
                $r['skip'] = $uqrs[$q->id]->skip;
                $r['correct_in_row'] = $uqrs[$q->id]->correct_in_row;
                $r['last_answer_at'] = $uqrs[$q->id]->last_answer_at;
            } else {
                $r['skip'] = null;
                $r['correct_in_row'] = null;
                $r['last_answer_at'] = null;
            }
            $has_answer = false;
            if (!empty($answers[$q->id])) {
                $r['answer'] = $answers[$q->id];
                $has_answer = true;
            };
            $has_answer_correct = false;
            $ro = [];
            foreach ($q->options as $o) {
                if ($has_answer) {
                    if ($answers[$q->id]['question_option_id'] == $o->id) {
                        if ($o->correct) {
                            $has_answer_correct = true;
                        }
                    }
                }
                $ro[] = [
                    'id' => $o->id,
                    'option' => $o->option,
                    'correct' => $o->correct,
                ];
            }
            if ($filter == 'correct' && (($has_answer && !$has_answer_correct) || !$has_answer)) {
                continue;
            }
            if ($filter == 'incorrect' && (($has_answer && $has_answer_correct) || !$has_answer)) {
                continue;
            }
            if ($filter == 'skipped' && $has_answer) {
                continue;
            }
            if ($shuffle) {
                shuffle($ro);
            }
            $r['options'] = $ro;
            $list[] = $r;
        }
        if ($shuffle) {
            shuffle($list);
        }
        return $list;
    }

}
