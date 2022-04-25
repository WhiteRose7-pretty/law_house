<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminTest extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_test';

    public function package(){
        return $this->belongsTo(Package::class);
    }

}
