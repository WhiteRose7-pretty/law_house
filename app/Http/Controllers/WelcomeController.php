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

        $promotions2 = Content::where('category', 'promo2')->get();
        foreach ($promotions2 as $promotion2){
            $m2 = $promotion2->getFirstMedia();
            if (!empty($m2)) {
                $m2 = $m2->getUrl('big');
            }
            $promotion2->image = $m2;
        }

        return view('welcome',['packages' => Package::where('visible', 1)->get(),
            'promotions' => $promotions, 'promotions_2' => $promotions2]);
    }

    public function index1()
    {
        $promotions = Content::where('category', 'promo')->get();
        foreach ($promotions as $promotion){
            $m = $promotion->getFirstMedia();
            if (!empty($m)) {
                $m = $m->getUrl('big');
            }
            $promotion->image = $m;
        }

        $promotions2 = Content::where('category', 'promo2')->get();
        foreach ($promotions2 as $promotion2){
            $m2 = $promotion2->getFirstMedia();
            if (!empty($m2)) {
                $m2 = $m2->getUrl('big');
            }
            $promotion2->image = $m2;
        }

        return view('welcome1',['packages' => Package::where('visible', 1)->get(),
            'promotions' => $promotions, 'promotions_2' => $promotions2]);
    }

}
