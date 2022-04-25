<?php

namespace App\Http\Controllers\Auth;

use App\ApiHash;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HashController extends Controller
{
    public function verify(Request $request) {

        $hash_id = $request->input('hash_id');
        $hash = ApiHash::find($hash_id);
        if (empty($hash)) {
            return response()->json(['error'=>true]);
        }
        $u = User::find($hash->user_id);
        return response()->json(['error'=>false,'user_id'=>$hash->user_id,'user_type'=>$hash->type,'user_name'=>$u->name]);
    }

    public function ping(Request $request) {

        return response()->json(Auth::user());
    }
}
