<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserQuestionCalendar extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_questions_calendar';

    static public function planned($user_id, $date, $questions_sets_ids =[]) {
        $f = self::select('question_id')->where('user_id',$user_id)->where('planned_at',$date);
        if (!empty($questions_sets_ids)) {
          $f = $f->whereIn('questions_set_id',$questions_sets_ids);
        }
        $tmp = $f->get();
        $ids = [];
        foreach($tmp as $q) {
            $ids[]=$q->question_id;
        }
        return $ids;
    }
}
