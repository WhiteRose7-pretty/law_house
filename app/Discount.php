<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    function packages() {
        return $this->belongsTo('App\Package');
    }
}
