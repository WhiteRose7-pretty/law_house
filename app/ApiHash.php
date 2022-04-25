<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiHash extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'api_hashes';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    static public function checkHash($id,$type=null) {
        $h = ApiHash::find($id);
        if (empty($type)) {
            return !empty($h);
        }
        if (empty($h)) {
            return false;
        }
        $types = $type=='admin' ? ['admin'] : ($type == 'subadmin' ? ['admin', 'subadmin'] : ($type=='editor' ? ['admin','editor', 'subadmin'] : ['admin', 'subadmin','editor','user']));
        return in_array($h->type, $types);
    }


    static public function checkHashStatus($id) {
        $h = ApiHash::find($id);

        if (empty($h)) {
            return false;
        }

        return $h->status;
    }


    static public function makeNewHash($user, $with_session=true) {
        static::logoutOtherDevice($user);
        $hash = new ApiHash();
        $hash->id = Str::random(16);
        $hash->user_id = $user->id;
        $hash->type = $user->type;
        $hash->status = 1;
        if ($with_session) {
          $hash->session_id = session()->getId();
          session()->save();
        }
        $hash->save();
    }

    static public function logoutOtherDevice($user){
        $hashes = ApiHash::where('user_id', $user->id)->get();
        foreach ($hashes as $item){
            $item->status = 0;
            $item->save();
        }
    }

    static public function mySessionHashId() {
        $session_id = session()->getId();
        $hash = ApiHash::where('session_id', $session_id)->first();
        if (empty($hash)) {
            return null;
        }
        return $hash->id;
    }
}
