<?php

namespace App;

use App\Question;
use App\UserGame;
use App\UserGameParticipant;
use App\UserGameParticipantAnswer;
use App\UserQuestionRepeat;


use Illuminate\Database\Eloquent\Model;

class UserGameQuestion extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_games_questions';

    public function answer() {
        return $this->belongsTo('App\UserGameParticipantAnswer','user_question_answer_id');
    }

    static public function continueGame(UserGame $g, UserGameParticipant $p) {
        $ids = self::ids($g->id,UserGameParticipantAnswer::answeredQuestionsIds($p));
        return Question::processList($p->user_id,$ids,true);
    }

    static public function fullGame(UserGame $g, UserGameParticipant $p) {
        $ids = self::ids($g->id);
        return Question::processList($p->user_id,$ids,true);
    }

    static public function ids($user_game_id, $ommit = []) {
        $q = self::select('question_id')->where('user_game_id',$user_game_id);
        if (!empty($ommit)) {
            $q = $q->whereNotIn('question_id', $ommit);
        }
        $tmp = $q->get();
        $res = [];
        foreach($tmp as $r) {
          $res[] = $r->question_id;
        }
        return $res;
    }


}
