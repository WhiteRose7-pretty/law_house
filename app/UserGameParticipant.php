<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserGameParticipant extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_games_participants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'user_game_id', 'owner', 'name',
    ];

    function answers() {
        return $this->hasMany('App\UserGameParticipantAnswer','user_game_participant_id');
    }

    function user_game() {
        return $this->belongsTo('App\UserGame','user_game_id');
    }

    static public function gameIds($user_id) {
        $t = self::select('user_game_id')->where('user_id',$user_id)->get();
        $o = [];
        foreach($t as $r) {
          $o[] = $r->user_game_id;
        }
        return $o;
    }

    static public function addToGame($user_game_id, User $user, $owner = false) {
        $p = self::where('user_game_id',$user_game_id)->where('user_id',$user->id)->first();
        if (!empty($p)) {
            if (!empty($p->left_at)) {
                $p->left_at = null;
                $p->save();
                return $p;
            }
            throw new \Exception('Jesteś już w tej grze. Spróbuj odświeżyć stronę.');
        }
        $p = new self([
            'user_id' => $user->id,
            'user_game_id' => $user_game_id,
            'name' => $user->name . '('. $user->id .')',
            'owner' => $owner,
        ]);
        $p->save();
        return $p;
    }
}
