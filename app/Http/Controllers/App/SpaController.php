<?php

namespace App\Http\Controllers\App;

use App\ApiHash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SpaController extends Controller
{
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
        return view('layouts.app',['hash_id' => ApiHash::mySessionHashId()]);
    }
}
