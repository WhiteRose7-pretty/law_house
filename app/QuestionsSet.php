<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use app\Question;

class QuestionsSet extends Model
{
    function questions() {
        return $this->hasMany('App\Question');
    }

    public static function get_legal_id($id){
        $featured_question = Question::with('laws')->withCount('laws')->where('questions_set_id', $id)->whereHas('laws')->take(10)->get();
        foreach ($featured_question as $item)
        {
            if($item->laws_count>0){
                return $item->laws[0]->law_document_id;
            }
        }
        return 0;
    }
}
