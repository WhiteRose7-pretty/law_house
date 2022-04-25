<?php

namespace App;

use App\UserGameParticipant;
use Illuminate\Database\Eloquent\Model;

class UserGameParticipantAnswer extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_games_participants_answers';

    static public function answeredQuestionsIds(UserGameParticipant $p) {
        $tmp = self::select('question_id')->where('user_game_participant_id',$p->id)->get();
        $res = [];
        foreach($tmp as $r) {
          $res[] = $r->question_id;
        }
        return $res;
    }

    static public function fullGame(UserGameParticipant $p) {
        $tmp = self::where('user_game_participant_id',$p->id)->orderBy('question_id')->get();
        $res = [];
        foreach($tmp as $r) {
            $res[$r->question_id] = $r;
        }
        return $res;
    }

    public function answer() {
        return $this->belongsTo('App\UserQuestionAnswer','user_question_answer_id');
    }

    static public function full($test,$filter=null)
    {
        $tmp = UserGameParticipantAnswer::with('answer')->where('user_game_participant_id',$test->id)->get();
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
