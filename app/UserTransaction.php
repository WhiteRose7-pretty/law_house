<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTransaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_transactions';

    function user_package() {
        return $this->belongsTo('App\UserPackage', 'user_package_id');
    }
}
