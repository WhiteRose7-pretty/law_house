<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestionLaw extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'questions_laws';

    function law_document_element() {
        return $this->belongsTo('App\LawDocumentElement');
    }
}
