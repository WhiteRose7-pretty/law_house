<?php

namespace App\Http\Controllers;

use App\Package;
use App\Content;
// use App\PackageSet;
// use App\User;
// use App\UserPackage;
// use App\UserPackageQuestionsSet;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WelcomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $promotions = Content::where('category', 'promo')->get();
        foreach ($promotions as $promotion){
            $m = $promotion->getFirstMedia();
            if (!empty($m)) {
                $m = $m->getUrl('big');
            }
            $promotion->image = $m;
        }

        return view('welcome',['packages' => Package::where('visible', 1)->get(),
            'promotions' => $promotions]);
    }

}
