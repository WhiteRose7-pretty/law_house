<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_packages';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
