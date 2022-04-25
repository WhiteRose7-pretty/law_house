<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;

class EmailChangeController extends Controller
{
    public function index($code) {

        $u = User::where('email_new_hash',$code)->first();

        $error = false;

        if (!empty($u)) {

            $c = User::where('email',$u->email_new)->count();

            if ($c) {
                $error = "Istnieje już konto o takim adresie mailowym: {$u->email_new}.";
            } else {
                $u->email = $u->email_new;
                $u->email_new = null;
                $u->email_new_hash = null;
                $u->save();
            }

        } else {
            $error = 'Ten kod weryfikacyjny jest nieaktualny!';
        }

        return view('email-change', ['error'=>$error]);
    }
}
