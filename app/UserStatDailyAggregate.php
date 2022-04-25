<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserStatDailyAggregate extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_stats_daily_aggregate';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'question_id', 'questions_set_id', 'date', 'change', 'new', 'repeat', 'answers', 'correct', 'incorrect', 'correct_in_row', 'all_answers', 'all_correct', 'all_incorrect', 'all_repeat',
    ];
}
