<?php

namespace App;

use App\UserQuestionRepeat;
use Illuminate\Database\Eloquent\Model;

class UserQuestionAnswer extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_questions_answers';

    protected $fillable = [
        'user_id', 'question_id', 'question_option_id', 'correct'
    ];

    public function question() {
        return $this->belongsTo('App\Question','question_id');
    }
}
