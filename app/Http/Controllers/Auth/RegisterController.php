<?php

namespace App\Http\Controllers\Auth;

use App\ApiHash;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Package;
use App\User;
use App\UserPackage;
use App\UserPackageQuestionsSet;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers {
        register as traitRegister;
    }

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }


    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $response = $this->traitRegister($request);

        if (Auth::user()) {
            session()->save();
            ApiHash::makeNewHash(Auth::user());
            $this->getFreePackages();
        }

        return $response;
    }

    protected function getFreePackages() {
        $uid = Auth::user()->id;
        $tmp = Package::where('free',1)->with('sets')->get();
        $list = [];
        foreach( $tmp as $p ) {
            $list[$p->id] = [
                'name' => $p->name,
                'sets' => [],
            ];
            foreach($p->sets as $s) {
                $list[$p->id]['sets'][] = $s->questions_set_id;
            }
        }
        foreach($list as $pid => $pack) {
            $p = new UserPackage();
            $p->user_id = $uid;
            $p->package_id = $pid;
            $p->name = $pack['name'];
            $p->type = 'free';
            $p->save();
            foreach($pack['sets'] as $sid) {
                $ps = new UserPackageQuestionsSet();
                $ps->user_id = $uid;
                $ps->user_package_id = $p->id;
                $ps->questions_set_id = $sid;
                $ps->free = 1;
                $ps->save();
            }
        }
    }
}
