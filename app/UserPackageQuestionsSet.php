<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UserPackageQuestionsSet extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_packages_questions_sets';

    static public function available_ids($user_id)
    {
        $tmp = UserPackageQuestionsSet::with('user_package')->where('user_id',$user_id)->where(function($query){
            $query->where('free', 1)->orWhere('valid_until', '>=', date('Y-m-d H:i:s'));
        })->whereHas('user_package', function ($query){
            $query->whereNotNull('package_id');
        })->get();

        $ids=[];
        foreach($tmp as $r) {
            $ids[]=$r->questions_set_id;
        }

        return $ids;
    }

    static public function available($user_id)
    {
      $tmp = UserPackageQuestionsSet::where('user_id',$user_id)->where(function($query){
          $query->where('free', 1)->orWhere('valid_until', '>=', date('Y-m-d H:i:s'));
      })->get();

      $ids=[];
      foreach($tmp as $r) {
          $ids[]=$r;
      }
      return $ids;
    }

    function user_package(){
        return $this->belongsTo('App\UserPackage');
    }
}
