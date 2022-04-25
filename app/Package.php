<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    function sets() {
       return $this->hasMany('App\PackageSet');
    }
}
