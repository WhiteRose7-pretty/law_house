<?php

namespace App\Http\Controllers\App;

use Mail;
use App\ApiHash;
use App\User;
use App\Http\Controllers\Controller;
use App\Mail\EmailChangeMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApiUserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function user(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $u = User::find($h->user_id);
        return response()->json($u);
    }

    public function userUpdate(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $un = (object)$request->input('user');
        $u = User::formInput($h->user_id, $un);
        return response()->json($u);
    }

    public function userPasswordUpdate(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $u = User::find($h->user_id);
        $pc = $request->input('password_current');
        $p = $request->input('password');
        $p2 = $request->input('password_confirmation');
        if (!Hash::check($pc, $u->password)) {
            return response()->json(['error' => 'Twoje dotychczasowe hasło jest inne! Spróbuj jeszcze raz.']);
        }
        if ($p!==$p2) {
            return response()->json(['error' => 'Nowe hasło i potwierdzenie hasła różnią się! Spróbuj jeszcze raz.']);
        }
        $u->password = Hash::make($p);
        $u->save();
        return response()->json([]);
    }

    public function remove(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $pc = $request->input('password');
        $u = User::find($h->user_id);

        if (!Hash::check($pc, $u->password)) {
            return response()->json(['message' => 'Złe hasło.'],401);
        }

        $id = $u->id;

        DB::beginTransaction();
        DB::statement('delete from users where id = '. $id);
        $ux = User::find($id);
        if (empty($ux)) {
            DB::statement('delete from sessions where user_id = '. $id);
            DB::commit();
            return response()->json([]);
        }
        DB::rollBack();
        return response()->json(['message' => 'Coś poszło nie tak skontaktuj się z administratorem.'],401);
    }

    public function email(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $u = User::find($h->user_id);
        $pc = $request->input('password');
        $ne = $request->input('email');

        if (!Hash::check($pc, $u->password)) {
            return response()->json(['message' => 'Złe hasło.'],401);
        }

        $c = User::where('email',$ne)->count();

        if ($c) {
            return response()->json(['message' => 'Taki email już istnieje w bazie.'],401);
        }

        $u->email_new = $ne;
        $u->email_new_hash = Str::random(16);
        $u->save();

        Mail::to($ne)->send(new EmailChangeMessage(
            $u->name,
            route('verify.email.change',['code'=>$u->email_new_hash])
        ));
        return response()->json([]);
    }
}
