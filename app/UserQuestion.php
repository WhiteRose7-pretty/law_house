<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\LawDocumentElement;

class UserQuestion extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_questions';

    function options()
    {
        return $this->hasMany('App\QuestionOption', 'question_id');
    }

    function laws() {
        return $this->hasMany('App\QuestionLaw', 'question_id');
    }

    static public function available_ids($user_id, $questions_set_ids, $limit = 0, $law_ids=null, $set_date=null)
    {
        if($law_ids && $set_date){
            $tmp = UserQuestion::with(['laws'])->select('id')->where('user_id', $user_id)->whereIn('questions_set_id', $questions_set_ids)
                ->where(function ($query) use($law_ids, $set_date){
                    $query->whereHas('laws', function ($tmp) use ($law_ids, $set_date) {
                        $tmp->whereIn('law_document_element_id', $law_ids)->where(function ($query) use($set_date){
                            $query->whereDate('start_date','<=', $set_date)->orWhereNull('start_date');;
                        })->where(function ($query1) use($set_date){
                            $query1->whereDate('end_date','>=', $set_date)->orWhereNull('end_date');
                        });
                    })->orWhereDoesntHave('laws');
                })
                ->get();
        }
        else{
            $tmp = UserQuestion::with(['laws'])->select('id')->where('user_id', $user_id)->whereIn('questions_set_id', $questions_set_ids)
                ->get();
        }

        $all = [];
        foreach ($tmp as $r) {
            $all[] = $r->id;
        }
        if (empty($limit)) {
            return $all;
        }
        if ($limit > count($all)) {
            return $all;
        }
        shuffle($all);
        return array_slice($all, 0, $limit);
    }
}
