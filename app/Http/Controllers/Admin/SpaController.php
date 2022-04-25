<?php

namespace App\Http\Controllers\Admin;

use App\ApiHash;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpaController extends Controller
{
    /**
     * Where to redirect if wrong authority
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
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->type == 'user') {
            return redirect($this->redirectTo);
        }

        return view('layouts.admin',['hash_id' => ApiHash::mySessionHashId()]);
    }
}
