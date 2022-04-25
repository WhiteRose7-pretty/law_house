<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserGame extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_games';

    function questions() {
        return $this->hasMany('App\UserGameQuestion','user_game_id');
    }

    function participants() {
        return $this->hasMany('App\UserGameParticipant','user_game_id')
            ->orderBy('questions_answered_correct_count','desc')
            ->orderBy('completion_seconds','desc');
    }

    function regulation() {
        return $this->belongsTo('App\Content', 'regula_id');
    }
}
