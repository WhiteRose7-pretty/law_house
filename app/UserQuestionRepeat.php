<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UserQuestionRepeat extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_questions_repeats';

    static protected $yh_settings=[];
    static protected $yh_list=[];
    static protected $yh_is_repeat=false;
    static protected $yh_list_saveme=[];

    public static function batchReady($user_id, $questions_ids, $is_repeat=false) {
        self::$yh_is_repeat = $is_repeat;
        self::batchReadySettings($user_id);
        $tmp = self::where('user_id',$user_id)->whereIn('question_id',$questions_ids)->get();
        $tmp = empty($tmp) ? [] : $tmp;
        foreach($tmp as $qr) {
            self::$yh_list[$qr->question_id] = $qr;
        }
        foreach($questions_ids as $id) {
            if (empty(self::$yh_list[$id])) {
                self::$yh_list[$id] = new UserQuestionRepeat();
                self::$yh_list[$id]->user_id = $user_id;
                self::$yh_list[$id]->question_id = $id;
            }
        }
    }

    protected static function batchReadySettings($user_id) {
        $u = User::find($user_id);
        self::$yh_settings = [
            0 => (24*60*60 * $u->repeat_incorrect),
            1 => (24*60*60 * $u->repeat_1_correct),
            2 => (24*60*60 * $u->repeat_2_correct),
            3 => (24*60*60 * $u->repeat_3_correct),
            4 => (24*60*60 * $u->repeat_4_correct),
            5 => (24*60*60 * $u->repeat_5_correct),
        ];
    }

    public static function check_repeat($question_id){
        $question = Question::find($question_id);
        return $question->questions_set->repeatable;
    }

    public static function batchOne($question_id, $correct) {
        if (empty(self::$yh_list[$question_id])) {
            throw new Exception('User Question Repeat Batch Not Ready');
        }
        if (!static::check_repeat($question_id)){
            return false;
        }
        self::$yh_list_saveme[$question_id]=$question_id;
        if (self::$yh_is_repeat) {
            self::$yh_list[$question_id]->last_repeat_at = date('Y-m-d H:i:s');
        }
        if (empty($correct)) {
            self::$yh_list[$question_id]->last_incorrect_at = date('Y-m-d H:i:s');
            self::$yh_list[$question_id]->correct_in_row = 0;
            self::$yh_list[$question_id]->skip = false;
        } else {
            self::$yh_list[$question_id]->last_correct_at = date('Y-m-d H:i:s');
            self::$yh_list[$question_id]->correct_in_row += 1;
        }
        self::$yh_list[$question_id]->last_answer_at = date('Y-m-d H:i:s');
        if (self::$yh_list[$question_id]->correct_in_row>5) {
            self::$yh_list[$question_id]->next_repeat_at = null;
            return true;
        }
        if (self::$yh_list[$question_id]->skip) {
            self::$yh_list[$question_id]->next_repeat_at = null;
            return false;
        }

        self::$yh_list[$question_id]->next_repeat_at = date('Y-m-d H:i:s', time() + self::$yh_settings[self::$yh_list[$question_id]->correct_in_row]);
        return true;
    }

    public static function batchSave() {
        $i = 0;
        foreach(self::$yh_list_saveme as $qid) {
            self::$yh_list[$qid]->save();
            $i = $i + 1;
        }
        return $i;
    }

    public static function listById($user_id,$ids) {
        $tmp = UserQuestionRepeat::where('user_id',$user_id)->whereIn('question_id',$ids)->get();
        $res = [];
        foreach($tmp as $r) {
            $res[$r->question_id] = $r;
        }
        return $res;
    }
}
