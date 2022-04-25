<?php

namespace App\Http\Controllers;

//use Mail;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Messages;

class ContactController extends Controller
{

    public function form(Request $request, $ok=false) {
        $message = $request->get('message', '');
        return view('contact', ['user'=>Auth::user(),'ok'=>$ok, 'r_message'=>$message]);
    }

    public function send(Request $request) {

        $user = Auth::user();

        if (!empty($user)) {
            extract( $request->validate([
                'message' => 'required',
                'r_message' => '',
            ]) );
            $name = $user->name;
            $email = $user->email;
            if($r_message){
                $message = $r_message .'<br>'. $message;
            }

        } else {
            extract( $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'message' => 'required',
            ]) );
        }

        Mail::to('kontakt@iusvitae.pl')->send(new ContactMessage($name, $email, $message, $user, $email));

        Mail::to($email)->send(new ContactMessage('testy_admin','wbednarczyk@iusvitae.pl'
            ,$message, '', 'kontakt@iusvitae.pl',true));

        $message_obj = new Messages();
        $message_obj->sender = 'kontakt@iusvitae.pl';
        $message_obj->receiver = $email;
        $message_obj->subject = 'Potwierdzenie wysłania wiadomości';
        $message_obj->content = $message;
        $message_obj->type = "confirm email";
        $message_obj->save();

        return redirect()->route('contact',['ok'=>'wyslano']);
    }
}
