<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserQuestionNewCount extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_questions_new_count';

    public static function listFor($user_id) {
        $tmp = self::where('user_id',$user_id)->get();
        $res = [];
        foreach($tmp as $r) {
            $res[$r->questions_set_id] = $r->count;
        }
        return $res;
    }
}
