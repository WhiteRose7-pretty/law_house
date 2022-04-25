<?php

namespace App;

use App\Question;
use App\UserQuestionAnswer;
use Illuminate\Database\Eloquent\Model;

class UserTestQuestion extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_tests_questions';

    public function answer() {
        return $this->belongsTo('App\UserQuestionAnswer','user_question_answer_id');
    }

    static public function unanswered($test, $repeat)
    {
        $tmp = self::select('question_id')->where('user_test_id',$test->id)->whereNull('user_question_answer_id')->get();
        $ids = [];
        foreach($tmp as $t) {
            $ids[] = $t->question_id;
        }
        return Question::processList($test->user_id,$ids,true);
    }

    static public function full($test,$filter=null)
    {
        $tmp = UserTestQuestion::with('answer')->where('user_test_id',$test->id)->get();
        $ids = [];
        $answers = [];
        foreach($tmp as $t) {
            $ids[] = $t->question_id;
            if (!empty($t->answer)) {
                $answers[$t->question_id] = [
                    'question_option_id' => $t->answer->question_option_id,
                ];
            }
        }
        return Question::processList($test->user_id,$ids,false,$answers,$filter);
    }
}
