<?php

namespace App\Http\Controllers;

use App\Content;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContentController extends Controller
{

    protected $limit = 20;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function news($page=1) {

        $pages = ceil(Content::where('category','news')->whereNotNull('published_at')->count()/$this->limit);

        if ($page>$pages||$page<1) {
            abort(404);
        }

        $skip = ($page-1)*$this->limit;

        $list = Content::where('category','news')->whereNotNull('published_at')->orderby('published_at', 'desc')->skip($skip)->take($this->limit)->get();

        return view('news',compact('page','pages','list'));
    }

    public function publish($id) {
        $u = Auth::user();
        if (empty($u) || !in_array($u->type,['admin', 'subadmin','editor'])) {
            abort(404);
        }
        $c = Content::find($id);
        if (!empty($c->published_at)) {
            return redirect()->route($c->category, ['title_uri' => $c->title_uri]);
        }
        $c->published_at = date('Y-m-d H:i:s');
        $c->save();
        $excerpt = '';
        $socket_url = config('custom.socketio_url');
        $notify_url = $socket_url.'/notifications/newarticle';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$notify_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('title' => $c->title, 'message' => $excerpt)));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        $info = curl_getinfo($ch);

        curl_close($ch);

        Log::debug('socket log: '.$info['url']);
        Log::debug('socket log: '.serialize($server_output));

        return redirect()->route($c->category, ['title_uri' => $c->title_uri]);
    }

    public function view(Request $request, $title_uri)
    {
        $categories = [
            'aktualnosci' => 'news',
            'czytaj' => 'promo',
            'czytaj2' => 'promo2',
            'informacje' => 'info',
            'przepisy-prawne' => 'regula',
        ];
        $tmp = explode('/',$request->path());
        if (!array_key_exists($tmp[0],$categories)) {
            abort(404);
        }
        $c = Content::where('category',$categories[$tmp[0]])->where('title_uri',$title_uri)->first();
        if (empty($c)) {
            abort(404);
        }
        $m = '';
        if ($c->category != 'info') {
            $m = $c->getFirstMedia();
            if (!empty($m)) {
                $m = $m->getUrl('big');
            }
        }

        $u = Auth::user();
        if (!empty($u) && in_array($u->type,['admin','editor', 'subadmin'])) {
            return view('content', ['content'=>$c,'image'=>$m]);
        }

        if (empty($c->published_at)) {
            return abort(404);
        }

        if ($c->private && (empty($u) || !in_array($u->type,['admin','editor', 'subadmin']))) {
            return abort(404);
        }

        return view('content', ['content'=>$c,'image'=>$m]);
    }

    public function regulations(Request $request)
    {
        $regulations = Content::where('category', '=', 'regula')->get();
        return response()->json($regulations);
    }
}
