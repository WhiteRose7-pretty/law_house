<?php

namespace App\Http\Controllers\Admin;

use App\ApiHash;
use App\Discount;
use App\Package;
use App\PackageSet;
use App\Payments\Fakturownia;
use App\QuestionsSet;
use App\UserPackage;
use App\UserPackageQuestionsSet;
use App\UserTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class ApiPackagesController extends Controller
{
    public function userPackages(Request $request)
    {
        $user_id = $request->input('user_id');
        return response()->json(UserPackage::where('user_id', $user_id)->orderBy('valid_until', 'desc')->get());
    }

    public function userTransactions(Request $request)
    {
        $user_id = $request->input('user_id');
        return response()->json(UserTransaction::with('user_package')->where('user_id', $user_id)->whereNotNull(['discount_code', 'user_package_id'])->orderBy('verified_at', 'desc')->get());
    }

    public function discounts(Request $request)
    {
        return response()->json([
            'results' => Discount::with('packages')->orderBy('id', 'desc')->get(),
        ]);
    }

    public function discountsAdd(Request $request)
    {
        $dn = $request->input('discount');

        $d = new Discount();
        $d->code = $dn['code'];
        if (!empty($dn['valid_until'])) {
            $d->valid_until = date('Y-m-d H:i:s',strtotime($dn['valid_until'] . ' 23:59:59'));
        }
        if (!empty($dn['start_date'])) {
            $d->start_date = date('Y-m-d H:i:s',strtotime($dn['start_date'] . ' 00:00:00'));
        }
        $d->discount = $dn['discount'];
        $d->packages_id = $dn['package'];
        $d->period = $dn['period'];
        $d->type = $dn['type'];
        $d->save();
        return response()->json();
    }

    public function packages(Request $request)
    {
        $setting = $request->post('with_sets', true);
        $count = Package::count();
        if($setting){
            $results = Package::with('sets')->orderByDesc('id')->get();
        }
        else{
            $count = Package::count();
            $results = Package::get();
        }
        return response()->json([
            'count' => $count,
            'results' => $results,
        ]);
    }

    public function remove(Request $request)
    {
        $p = Package::find($request->input('id'));
        $p->delete();
        if (empty($p)) {
            return response()->json(['message'=>'Content not found with the given ID'], 404);
        }
        return response()->json();
    }

    public function transactions(Request $request)
    {
        $l = $request->input('limit');
        $t = $request->input('type');
        $p = $request->input('page');

        switch($t) {
            case 'paid':
                $q = UserTransaction::whereNotNull('verified_at');
                break;
            case 'unpaid':
                $q = UserTransaction::whereNull('verified_at');
                break;
            default:
                $q = UserTransaction::whereNotNull('id');
                break;
        }

        $count = $q->count();
        $pages = ceil($count/$l);
        $page = $p > $pages ? $pages : $p;
        $skip = ($page-1)*$l;
        $rr = $q->orderBy('id','desc')->skip($skip)->take($l)->get();
        $results = [];
        foreach($rr as $r) {
            $r->user = unserialize($r->user_invoice_data);
            $results[] = $r;
        }
        return response()->json([
            'page' => $page,
            'pages' => $pages,
            'count' => $count,
            'results' => $results,
            'urlFV' => Fakturownia::urlPDF(),
        ]);
    }

    public function update(Request $request)
    {
        $pn = $request->input('package');

        if (empty($pn['id'])) {
            $p = new Package();
        } else {
            $p = Package::find($pn['id']);
        }

        if (empty($p)) {
            return response()->json(['message'=>'Content not found with the given ID'], 404);
        }

        $p->name = $pn['name'];
        $p->info = $pn['info'];
        $p->free = $pn['free'];
        $p->visible = $pn['visible'];
        $p->price1m = empty($pn['free']) ? $pn['price1m'] : 0;
        $p->price3m = empty($pn['free']) ? $pn['price3m'] : 0;
        $p->price1y = empty($pn['free']) ? $pn['price1y'] : 0;
        $p->save();

        $ids=[];
        foreach($pn['sets'] as $s) {
            if (!empty($s['selected'])) {
                $ids[$s['id']] = $p->id;
            }
        }

        $sets = PackageSet::where('package_id',$p->id)->get();
        $user_packages = UserPackage::where('package_id', $p->id)->get();
        foreach($sets as $s) {
            if (empty($ids[$s->questions_set_id])) {
                foreach($user_packages as $user_package) {
                    $u_pqs1 = UserPackageQuestionsSet::where('user_package_id', $user_package->id)->
                    where('questions_set_id', $s->questions_set_id)->get();
                    foreach ($u_pqs1 as $u_pq) {
                        $u_pq->delete();
                    }
                }
                $s->delete();

            } else {
                unset($ids[$s->questions_set_id]);
            }
        }

        if (!empty($ids)) {
            foreach($ids as $questions_set_id => $package_id) {
                $ps = new PackageSet(compact('questions_set_id','package_id'));
                $ps->save();

                foreach($user_packages as $user_package){
                    $u_pqs = new UserPackageQuestionsSet();
                    $u_pqs->user_package_id = $user_package->id;
                    $u_pqs->user_id = $user_package->user_id;
                    $u_pqs->questions_set_id = $questions_set_id;
                    if($user_package->type == 'free'){
                        $u_pqs->free = 1;
                    }
                    $u_pqs->valid_until = $user_package->valid_until;
                    $u_pqs->save();
                }
            }
        }
        return response()->json();
    }

}
