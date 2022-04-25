<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackageSet extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'packages_sets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'questions_set_id','package_id'
    ];
}
