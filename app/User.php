<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'user',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'surname', 'email', 'password', 'address_street_name', 'address_street_number', 'address_apartment_number', 'address_city', 'address_post_code', 'tax_nip', 'request_invoice'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function packages_questions_sets() {
        return $this->hasMany('App\UserPackageQuestionsSet');
    }

    static public function formInput($user_id, $d, $newsletter=false) {
        $u = self::find($user_id);
        $u->salutation = $d->salutation;
//        if ($newsletter) {
//            $u->newsletter = $d->newsletter;
//        }
        $u->name = $d->name;
        $u->surname = $d->surname;
        $u->request_invoice = $d->request_invoice;
        if ($u->request_invoice) {
            $u->address_street_name = $d->address_street_name;
            $u->address_street_number = $d->address_street_number;
            $u->address_apartment_number = $d->address_apartment_number;
            $u->address_city = $d->address_city;
            $u->address_post_code = $d->address_post_code;
            $u->address_company = $d->address_company;
            $u->tax_nip = $d->tax_nip;
        }

        $u->repeat_incorrect = $d->repeat_incorrect;
        $u->repeat_1_correct = $d->repeat_1_correct;
        $u->repeat_2_correct = $d->repeat_2_correct;
        $u->repeat_3_correct = $d->repeat_3_correct;
        $u->repeat_4_correct = $d->repeat_4_correct;
        $u->repeat_5_correct = $d->repeat_5_correct;

        $u->save();
        return $u;
    }
}
