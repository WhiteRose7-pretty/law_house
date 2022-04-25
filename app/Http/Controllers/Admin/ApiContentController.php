<?php

namespace App\Http\Controllers\Admin;

use App\Content;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiContentController extends Controller
{
    public function content(Request $request)
    {
        $c = Content::where('id',$request->input('id'))->where('category',$request->input('category'))->first();
        if (empty($c)) {
            return response()->json(['message'=>'Content not found with the given ID'], 404);
        }
        $m = $c->getFirstMedia();
        $u = [];
        if (!empty($m)) {
            $u['image'] = $m->getUrl('big');
        }
        return response()->json(array_merge($c->toArray(),$u));
    }

    public function contentImageRemove(Request $request)
    {
        $c = Content::find($request->input('id'));
        if (empty($c)) {
            return response()->json(['message'=>'Content not found with the given ID'], 404);
        }
        $m = $c->getFirstMedia();
        if (!empty($m)) {
            $m->delete();
        }
        return response()->json([]);
    }

    public function contentImageUpload(Request $request)
    {
        $c = Content::find($request->input('id'));
        if (empty($c)) {
            return response()->json(['message'=>'Content not found with the given ID'], 404);
        }
        $c->addMedia($request->file('image')->getPathName())->toMediaCollection();
        $m = $c->getFirstMedia();
        $u = $m->getUrl('big');
        return response()->json(['image'=>$u]);
    }

    public function contentList(Request $request)
    {
        $c = $request->input('category');
        $l = $request->input('limit');
        $q = $request->input('query');
        $p = $request->input('page');

        if (empty($q)) {
            $count = Content::where('category',$c)->whereNull('deleted_at')->count();
            $pages = ceil($count/$l);
            $page = $p > $pages ? $pages : $p;
            $skip = ($page-1)*$l;
            $results = Content::select('id','title','created_at','updated_at','published_at')->where('category',$c)
                ->whereNull('deleted_at')->orderby('published_at', 'asc')->skip($skip)->take($l)->get();
            return response()->json([
                'page' => $page,
                'pages' => $pages,
                'count' => $count,
                'results' => $results,
            ]);
        }

        $count = Content::where('category',$c)->where('title', 'like', $q.'%')->whereNull('deleted_at')->count();
        $pages = ceil($count/$l);
        $page = $p > $pages ? $pages : $p;
        $skip = ($page-1)*$l;
        $results = Content::select('id','title','created_at','updated_at','published_at')->where('category',$c)
            ->where('title', 'like', $q.'%')->whereNull('deleted_at')->orderby('published_at', 'asc')
            ->skip($skip)->take($l)->get();
        return response()->json([
            'page' => $page,
            'pages' => $pages,
            'count' => $count,
            'results' => $results,
        ]);
    }

    public function contentRemove(Request $request)
    {
        $c = Content::find($request->input('id'));
        if (empty($c)) {
            return response()->json([], 404);
        }
        $c->delete();
        return response()->json([]);
    }

    public function contentUpdate(Request $request)
    {
        $cn = (object)$request->input('content');
        if (empty($cn->id)) {
            $c = new Content();
            $c->title = $cn->title;
            $c->category = $cn->category;
            $c->save();
            return response()->json($c);
        }

        $c = Content::find($cn->id);
        if (empty($c)) {
            return response()->json(['message'=>'Content not found with the given ID'], 404);
        }

        $c->title = $cn->title;
        $c->lead = empty($cn->lead) ? '' : $cn->lead;
        $c->full = empty($cn->full) ? '' : $cn->full;
        if($c->category!=='info'){
            $c->generateUri();
        }
        $c->save();
        return response()->json($c);
    }
}
