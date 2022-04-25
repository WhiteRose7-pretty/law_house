<?php

namespace App\Http\Controllers\App;

use App\ApiHash;
use App\Discount;
use App\Package;
use App\PackageSet;
use App\Payments\Przelewy24;
use App\Payments\Fakturownia;
use App\User;
use App\UserPackage;
use App\UserPackageQuestionsSet;
use App\UserQuestionNew;
use App\UserQuestionNewCount;
use App\UserQuestionKnown;
use App\UserQuestionKnownCount;
use App\UserStatQuestionRepeat;
use App\UserTransaction;
use App\QuestionsSet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;


class ApiPackagesController extends Controller
{

    protected $vat = 0.23;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function available()
    {
        $tmp = Package::where('visible', 1)->get();
        $res = [];
        foreach ($tmp as $p) {
            $res[$p->id] = $p;
        }
        return response()->json($res);
    }

    public function transactionsReturn(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));

        return response()->json([
            'transaction' => UserTransaction::where('user_id', $h->user_id)->orderBy('id', 'desc')->first()
        ]);
    }

    public function transactions(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));

        $t = UserTransaction::where('user_id', $h->user_id)->orderBy('id', 'desc')->get();

        $tt = [];
        foreach ($t as $r) {
            $r->delta = time() - strtotime($r->created_at);
            $tt[] = $r;
        }

        return response()->json([
            'transactions' => $tt,
            'url' => Przelewy24::urlTransactionBase(),
            'urlFV' => Fakturownia::urlPDF()
        ]);

    }

    public function buyStart(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));

        $pn = (object)$request->input('package');
        $un = (object)$request->input('user');
        $m = $request->input('months');
        $d = $request->input('discount');

        $t = $m < 12 ? $m . 'm' : '1y';
        $u = User::formInput($h->user_id, $un, true);
        $p = Package::find($pn->id);
        $price = $this->price($p, $m);

        $discount_code = null;
        $discount = null;
        $discount_info = '';

        if (!empty($d['code'])) {
            $dd = Discount::where('code', $d['code'])->first();
            if (empty($dd)) {
            } elseif (!empty($dd->valid_until) && $dd->valid_until < date('Y-m-d H:i:s')) {
            } else {
                $discount = $dd->discount;
                $discount_code = $dd->code;
                $price = $this->price($p, $m, $discount);
                $discount_info = " (ze zniżką {$discount}%)";
                if ($dd->type == 'once use') {
                    $dd->active = false;
                }

                $dd->save();
            }
        }


        DB::beginTransaction();

        list($up, $sets) = $this->packagePrep($h->user_id, $pn->id, $t);
        if ($price['gross'] == 0) {
            $up->paid_at = date('Y-m-d H:i:s');
            switch ($up->type) {
                case '1m':
                    $date = date("Y-m-d H:i:s", strtotime("+1 month", time()));
                    break;
                case '3m':
                    $date = date("Y-m-d H:i:s", strtotime("+3 month", time()));
                    break;
                case '1y':
                    $date = date("Y-m-d H:i:s", strtotime("+12 month", time()));
                    break;
            }
            $up->valid_until = $date;
            $up->save();
            foreach ($sets as $s) {
                $s->valid_until = $up->valid_until;
                $s->save();
            }
            DB::commit();
            return response()->json([
                'url' => '/app/shop'
            ]);
        }


        $s = ['1m', '3m', '1y'];
        $r = ['1 miesiąc', '3 miesiące', '1 rok'];

        $ut = new UserTransaction();
        $ut->hash = Str::random(9);
        $ut->user_id = $h->user_id;
        $ut->user_package_id = $up->id;
        $ut->description = $p->name . ' (' . str_replace($s, $r, $t) . ')' . $discount_info;
        $ut->discount = $discount;
        $ut->discount_code = $discount_code;
        $ut->tax = round($this->vat * 100);
        $ut->amount_net = round($price['net'] * 100);
        $ut->amount_vat = round($price['vat'] * 100);
        $ut->amount_gross = round($price['gross'] * 100);
        $ut->bill_format = $u->request_invoice ? 'invoice' : 'receipt';
        $ut->user_invoice_data = serialize($u->toArray());
        $ut->save();

        $x = Przelewy24::register($u, $ut);

        if (empty($x)) {
            DB::rollback();
            return response()->json(['message' => 'Błąd rejestracji transakcji w serwisie płatniczym'], 409);
        }

        DB::commit();

        return response()->json([
            'ut' => $ut,
            'url' => Przelewy24::urlTransaction($ut)
        ]);
    }

    public function buyStatus(Request $request)
    {
        $xd = $request->all();
        $jd = json_decode(file_get_contents('php://input'));
        Log::debug('BUY STATUS RAW : ' . serialize($jd));
        Log::debug('BUY STATUS INPUT : ' . serialize($xd));
        $response = Przelewy24::verify($xd);
        Log::debug('BUY STATUS RETURN CURL : ' . serialize($response));
        Log::debug('BUY STATUS RETURN CURL CHECK : ' . (!empty($response->data->status) && $response->data->status == 'success' ? 'true' : 'false'));

        if (empty($response->data->status) || $response->data->status != 'success') {
            return;
        }

        DB::beginTransaction();

        $sid = explode(':', $xd['sessionId']);
        $ut = UserTransaction::where('hash', $sid[0])->where('id', $sid[1])->first();
        $ut->verified_at = date('Y-m-d H:i:s');
        $ut->status = 'verified';
        $ut->save();

        list($up, $sets) = $this->packageGet($ut->user_id, $ut->user_package_id);
        switch ($up->type) {
            case '1m':
                $date = date("Y-m-d H:i:s", strtotime("+1 month", time()));
                break;
            case '3m':
                $date = date("Y-m-d H:i:s", strtotime("+3 month", time()));
                break;
            case '1y':
                $date = date("Y-m-d H:i:s", strtotime("+12 month", time()));
                break;
        }

        $up->paid_at = date('Y-m-d H:i:s');
        $up->valid_until = $date;
        $up->save();

        foreach ($sets as $s) {
            $s->valid_until = $date;
            $s->save();
        }

        DB::commit();

        Fakturownia::add($ut);
    }

    protected function price($p, $m, $discount = 0)
    {
        switch ($m) {
            case 12:
                $pp = $p->price1y;
                break;
            case 3:
                $pp = $p->price3m;
                break;
            case 1:
                $pp = $p->price1m;
                break;
        }

        if ($discount) {
            $pp = round($pp * ((100 - $discount) / 100) * 100) / 100;
        }

        return [
            'gross' => number_format($pp, 2),
            'net' => number_format(round(($pp / (1 + $this->vat)) * 100) / 100, 2),
            'vat' => number_format(round(($pp - $pp / (1 + $this->vat)) * 100) / 100, 2),
        ];
    }

    public function form(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));

        $i = $request->input('package_id');
        $m = $request->input('months');
        $d = $request->input('discount');

        $p = Package::find($i);
        $price = $this->price($p, $m);

        $discount = null;
        $discount_error = null;
        $discount_price = null;

        if (!empty($d['code'])) {
            $dd = Discount::where('code', $d['code'])->where('active', true)->first();
            if ($dd->package_id) {
                if ($dd->package_id != $i) {
                    $dd = null;
                }
            }
            if ($dd->period) {
                if ($dd->period != $m) {
                    $dd = null;
                }
            }

            if (empty($dd)) {
                $discount_error = 'Kod nie istnieje lub nie jest przeznaczony dla pakietu.';
            } elseif (!empty($dd->valid_until) && $dd->valid_until < date('Y-m-d H:i:s')) {
                $discount_error = 'Kod nie jest już aktualny';
            } elseif (!empty($dd->start_date) && $dd->start_date > date('Y-m-d H:i:s')) {
                $discount_error = 'Kod nie jest teraz ważny.';
            } else {
                $discount = $dd->discount;
                $discount_price = $this->price($p, $m, $discount);
            }
        }

        return response()->json([
            'package' => $p,
            'price' => $price,
            'discount' => $discount,
            'discount_error' => $discount_error,
            'discount_price' => $discount_price,
        ]);
    }

    public function current(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        return response()->json(UserPackage::where('user_id', $h->user_id)->where(function ($query) {
            $query->where('type', 'free')->orWhere('valid_until', '>=', date('Y-m-d H:i:s'));
        })->orderBy('valid_until')->get());
    }


    protected function packagePrep($user_id, $package_id, $type = '1m')
    {
        $p = Package::with('sets')->find($package_id);

        $up = new UserPackage();
        $up->user_id = $user_id;
        $up->name = $p->name;
        $up->package_id = $p->id;
        $up->type = $type;
        $up->save();

        $sets = [];
        foreach ($p->sets as $s){
            $ups = UserPackageQuestionsSet::where('user_id', '=', $user_id)->where('questions_set_id', '=', $s->questions_set_id)->first();
            if(empty($ups)) {
                $ups = new UserPackageQuestionsSet();
            }
            $ups->user_id = $user_id;
            $ups->user_package_id = $up->id;
            $ups->questions_set_id = $s->questions_set_id;
            if ($type == 'free') {
                $ups->free = 1;
            }
            $ups->save();
            $sets[] = $ups;
        }

        return [$up, $sets];
    }

    protected function packageGet($user_id, $user_package_id)
    {
        $up = UserPackage::where('user_id', $user_id)->where('id', $user_package_id)->first();
        $sets = UserPackageQuestionsSet::where('user_id', $user_id)->where('user_package_id', $user_package_id)->get();
        return [$up, $sets];
    }

    public function quickfix(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        list($up, $sets) = $this->packagePrep($h->user_id, $request->input('package_id'));
        $up->paid_at = date('Y-m-d H:i:s');
        $up->valid_until = date("Y-m-d H:i:s", strtotime("+1 month", time()));
        $up->save();
        foreach ($sets as $s) {
            $s->valid_until = $up->valid_until;
            $s->save();
        }
        return response()->json();
    }

    public function addFree(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        list($up, $sets) = $this->packagePrep($h->user_id, $request->input('package_id'), 'free');
        $up->paid_at = null;
        $up->valid_until = null;
        $up->save();
        foreach ($sets as $s) {
            $s->valid_until = $up->valid_until;
            $s->save();
        }
        return response()->json();
    }

    public function repeats(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        Log::debug('timestamp 1: ' . Carbon::now()->toDateTimeString());
        $ids = UserPackageQuestionsSet::available_ids($h->user_id);
        Log::debug('timestamp 2: ' . Carbon::now()->toDateTimeString());


        $tmp = UserStatQuestionRepeat::where('user_id', $h->user_id)->whereIn('questions_set_id', $ids)->get();
        Log::debug('timestamp 3: ' . Carbon::now()->toDateTimeString());
        $stats = [];
        foreach ($tmp as $r) {
            $stats[$r->questions_set_id] = $r;
        }
        $stats_empty = [
            'correct_0' => 0,
            'correct_1' => 0,
            'correct_2' => 0,
            'correct_3' => 0,
            'correct_4' => 0,
            'correct_5' => 0,
            'correct_m' => 0,
            'today_repeat' => 0,
            'q_total' => 0,
            'empty_stats' => true,
        ];
        $tmp = QuestionsSet::withCount(['questions' => function (Builder $query) {
            $query->where('deleted', '=', '0');
        },])->whereIn('id', $ids)->orderBy(DB::raw('coalesce(`group`,`name`)'))->get();
        $results = [];
        $groups = [];
        $i = 1;
        Log::debug('timestamp3 : ' . Carbon::now()->toDateTimeString());
        foreach ($tmp as $r) {
            if (empty($r->group)) {
                $results[$i] = [
                    'id' => $r->id,
                    'name' => $r->name,
                    'total' => $r->questions_count,
                    'stats' => (empty($stats[$r->id]) ? $stats_empty : $stats[$r->id]),
                ];
                $i++;
                continue;
            }
            if (empty($groups[$r->group])) {
                $groups[$r->group] = $i;
                $results[$i] = [
                    'index' => $i,
                    'ids' => '',
                    'ida' => [],
                    'group' => $r->group,
                    'total' => 0,
                    'stats' => $stats_empty,
                    'list' => [],
                ];
                $i++;
            }
            $r_ = [
                'id' => $r->id,
                'name' => $r->name,
                'total' => $r->questions_count,
                'stats' => (empty($stats[$r->id]) ? $stats_empty : $stats[$r->id]),
            ];
            $results[$groups[$r->group]]['list'][] = $r_;
            if (empty($r_['stats']['empty_stats']) && !empty($results[$groups[$r->group]]['stats']['empty_stats'])) {
                unset($results[$groups[$r->group]]['stats']['empty_stats']);
            }
            $results[$groups[$r->group]]['ida'][] = $r_['id'];
            $results[$groups[$r->group]]['total'] += $r_['total'];
            $results[$groups[$r->group]]['stats']['correct_0'] += $r_['stats']['correct_0'];
            $results[$groups[$r->group]]['stats']['correct_1'] += $r_['stats']['correct_1'];
            $results[$groups[$r->group]]['stats']['correct_2'] += $r_['stats']['correct_2'];
            $results[$groups[$r->group]]['stats']['correct_3'] += $r_['stats']['correct_3'];
            $results[$groups[$r->group]]['stats']['correct_4'] += $r_['stats']['correct_4'];
            $results[$groups[$r->group]]['stats']['correct_5'] += $r_['stats']['correct_5'];
            $results[$groups[$r->group]]['stats']['correct_m'] += $r_['stats']['correct_m'];
            $results[$groups[$r->group]]['stats']['today_repeat'] += $r_['stats']['today_repeat'];
            $results[$groups[$r->group]]['stats']['q_total'] += $r_['stats']['q_total'];
        }
        Log::debug('timestamp 4: ' . Carbon::now()->toDateTimeString());
        return response()->json(['list' => $results]);
    }

    public function setsAvailable(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $ids = UserPackageQuestionsSet::available_ids($h->user_id);
        return response()->json(QuestionsSet::whereIn('id', $ids)->orderBy('name')->get());
    }


    public function setsTree(Request $request)
    {
        Log::debug('Called SetsTree: ' . Carbon::now()->toDateTimeString());
        $h = ApiHash::find($request->input('hash_id'));
        $ids = UserPackageQuestionsSet::available_ids($h->user_id);
        $tmp = QuestionsSet::withCount(['questions' => function (Builder $query) {
            $query->where('deleted', '=', '0');
        },])->whereIn('id', $ids)->orderBy(DB::raw('coalesce(`group`,`name`)'))->get();

//        $count_new_all = UserQuestionNewCount::listFor($h->user_id);
//        $count_known_all = UserQuestionKnownCount::listFor($h->user_id);

        $count_known_all = [];
        $count_new_all = [];

        $results = [];
        $groups = [];
        $i = 1;
        Log::debug('database read end: ' . Carbon::now()->toDateTimeString());
        foreach ($tmp as $r) {
            $count_new = empty($count_new_all[$r->id]) ? 100 : $count_new_all[$r->id];
            $count_known = empty($count_known_all[$r->id]) ? 100 : $count_known_all[$r->id];
            if (empty($r->group)) {
                $results[$i] = [
                    'id' => $r->id,
                    'name' => $r->name,
                    'total' => $r->questions_count,
                    'out_date' => $r->out_date,
                    'new' => $count_new,
                    'known' => $count_known,
                    'legal_id' => QuestionsSet::get_legal_id($r->id),
                    'selected' => 0,
                ];
                $i++;
                continue;
            }
            if (empty($groups[$r->group])) {
                $groups[$r->group] = $i;
                $results[$i] = [
                    'index' => $i,
                    'group' => $r->group,
                    'list' => [],
                    'total' => 0,
                    'new' => 0,
                    'known' => 0,
                    'selected' => 0,
                    'out_date' => false,
                ];
                $i++;
            }
            $results[$groups[$r->group]]['list'][] = [
                'id' => $r->id,
                'name' => $r->name,
                'total' => $r->questions_count,
                'out_date' => $r->out_date,
                'new' => $count_new,
                'known' => $count_known,
                'selected' => 0,
                'legal_id' => QuestionsSet::get_legal_id($r->id),
            ];
            $results[$groups[$r->group]]['total'] += $r->questions_count;
            $results[$groups[$r->group]]['new'] += $count_new;
            $results[$groups[$r->group]]['known'] += $count_known;
            $results[$groups[$r->group]]['out_date'] |= $r->out_date;
        }

        return response()->json($results);
    }
}
