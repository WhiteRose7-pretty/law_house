<?php

namespace App\Http\Controllers\Admin;

use App\AdminTest;
use App\ApiHash;
use App\Mail\AdminMessage;
use App\Messages;
use App\UserPackage;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ApiTestsController extends Controller
{

    public function tests(Request $request)
    {
        $count = AdminTest::count();
        $results = AdminTest::with('package')->get();
        return response()->json([
            'count' => $count,
            'results' => $results,
        ]);
    }

    public function remove(Request $request)
    {
        $p = AdminTest::find($request->input('id'));

        if (empty($p)) {
            return response()->json(['message'=>'Content not found with the given ID'], 404);
        }
        $p->delete();
        return response()->json();
    }

    public function update(Request $request)
    {
        $pn = $request->input('test');

        if (empty($pn['id'])) {
            $p = new AdminTest();
        } else {
            $p = AdminTest::find($pn['id']);
        }

        if (empty($p)) {
            return response()->json(['message'=>'Content not found with the given ID'], 404);
        }

        $p->name = $pn['name'];
        $p->package_id = $pn['package_id'];
        $p->questions = $pn['questions'];
        $p->counts_of_questions = $pn['counts_of_questions'];
        $p->save();

        return response()->json();
    }

    public function available(Request $request){
        $h = ApiHash::find($request->input('hash_id'));
        $user_packages = UserPackage::where('user_id', $h->user_id)->get();
        $packages_avaiable = [];
        foreach($user_packages as $item){
            array_push($packages_avaiable, $item->package_id);
        }
        $t = AdminTest::whereIn('package_id', $packages_avaiable)->get();
        return response()->json($t);
    }

    public function emails(Request $request)
    {
        $count = Messages::count();
        $results = Messages::get();
        return response()->json([
            'count' => $count,
            'results' => $results,
        ]);
    }

    public function send_email(Request $request){


        $email = $request->input('email');
        $subject = $request->input('subject');
        $content = $request->input('content');


        Mail::to($email)->send(new AdminMessage($email, $content, $subject));

        $message_obj = new Messages();
        $message_obj->sender = 'kontakt@iusvitae.pl';
        $message_obj->receiver = $email;
        $message_obj->subject = $subject;
        $message_obj->content = $content;
        $message_obj->type = "admin email";
        $message_obj->save();
    }

}
