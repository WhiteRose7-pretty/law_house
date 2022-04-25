<?php

namespace App\Http\Controllers\Admin;

use App\ApiHash;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiUsersController extends Controller
{
    public function users(Request $request)
    {
        $l = $request->input('limit');
        $q = $request->input('query');
        $p = $request->input('page');
        if (empty($q)) {
            $count = User::count();
            $pages = ceil($count/$l);
            $page = $p > $pages ? $pages : $p;
            $skip = ($page-1)*$l;
            $results = User::skip($skip)->take($l)->get();
            return response()->json([
                'page' => $page,
                'pages' => $pages,
                'count' => $count,
                'results' => $results,
            ]);
        }

        $count = User::where('email', 'like', $q.'%')
            ->orWhere('name', 'like', $q.'%')
            ->orWhere('surname', 'like', $q.'%')
            ->count();
        $pages = ceil($count/$l);
        $page = $p > $pages ? $pages : $p;
        $skip = ($page-1)*$l;
        $results = User::where('email', 'like', $q.'%')
            ->orWhere('name', 'like', $q.'%')
            ->orWhere('surname', 'like', $q.'%')
            ->skip($skip)->take($l)->get();
        return response()->json([
            'page' => $page,
            'pages' => $pages,
            'count' => $count,
            'results' => $results,
        ]);
    }

    public function usersRemove(Request $request)
    {
        $u = User::find($request->input('id'));
        if (empty($u) || !empty($u->email_verified_at)) {
            return response()->json([], 404);
        }
        $u->delete();
        return response()->json([]);
    }

    public function usersType(Request $request)
    {
        $u = User::find($request->input('id'));
        $t = $request->input('type');
        if (empty($u) || empty($t) || empty($u->email_verified_at)) {
            return response()->json([], 404);
        }
        $u->type=$t;
        $u->save();
        ApiHash::where('user_id', $u->id)->update(['type' => $t]);
        return response()->json([]);
    }
}
