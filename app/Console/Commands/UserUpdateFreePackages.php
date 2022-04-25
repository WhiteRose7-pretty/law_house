<?php

namespace App\Console\Commands;

use App\Package;
use App\PackageSet;
use App\User;
use App\UserPackage;
use App\UserPackageQuestionsSet;
use Illuminate\Console\Command;

class UserUpdateFreePackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iusvitae:users-update-free-packages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix all users with new free packages';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Processing...');

        $packages = $this->getFreePackages();
        $skip = 0;
        $limit = 50;
        do {
          $users = $this->getUsers($skip,$limit);
          if (empty($users)) {
              continue;
          }
          foreach($users as $uid => $sets) {
              $this->checkUser($uid,$sets,$packages);
          }
          $skip+=$limit;
        } while(count($users) < $limit == false);
    }

    protected function checkUser($uid,$sets,$packages) {
        foreach($packages as $pid => $pack) {
            $need_fix=false;
            foreach($pack['sets'] as $sid) {
                if (!in_array($sid,$sets)) {
                    $need_fix=true;
                }
            }
            if ($need_fix) {
                $p = new UserPackage();
                $p->user_id = $uid;
                $p->package_id = $pid;
                $p->name = $pack['name'];
                $p->type = 'free';
                $p->save();
                foreach($pack['sets'] as $sid) {
                    $ps = new UserPackageQuestionsSet();
                    $ps->user_id = $uid;
                    $ps->user_package_id = $p->id;
                    $ps->questions_set_id = $sid;
                    $ps->free = 1;
                    $ps->save();
                }
                $this->info('Fixed UID = ' . $uid);
            }
        }
    }

    protected function getUsers($skip=0,$take=50) {
        $tmp = User::with('packages_questions_sets')->skip($skip)->take($take)->get();
        if (empty($tmp)) {
            return [];
        }
        $list = [];
        foreach($tmp as $u) {
            $list[$u->id]=[];
            foreach($u->packages_questions_sets as $pqs) {
                $list[$u->id][]=$pqs->questions_set_id;
            }
        }
        return $list;
    }

    protected function getFreePackages() {
        $tmp = Package::where('free',1)->with('sets')->get();
        $list = [];
        foreach( $tmp as $p ) {
            $list[$p->id] = [
                'name' => $p->name,
                'sets' => [],
            ];
            foreach($p->sets as $s) {
                $list[$p->id]['sets'][] = $s->questions_set_id;
            }
        }
        return $list;
    }
}
