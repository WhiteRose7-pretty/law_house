<?php

namespace App\Http\Controllers\App;

use App\ApiHash;
use App\UserStatQuestionRepeat;
use App\UserStatDailyAggregateSetSummary;
use App\UserStatDailyAggregateSetSummary1;
use App\UserStatWeeklyAggregateSetSummary;
use App\UserStatWeeklyAggregateSetSummary1;
use App\UserStatMonthlyAggregateSetSummary;
use App\UserStatMonthlyAggregateSetSummary1;
use App\UserStatDailyAggregateTotalSummary;
use App\UserStatDailyAggregateTotalSummary1;
use App\UserStatWeeklyAggregateTotalSummary;
use App\UserStatWeeklyAggregateTotalSummary1;
use App\UserStatMonthlyAggregateTotalSummary;
use App\UserStatMonthlyAggregateTotalSummary1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;
class ApiStatsController extends Controller
{
    protected $settings = [
        'new' => 'Nowe',
        'repeat' => 'Powtórzenia',
        'answers' => 'Odpowiedzi',
        'correct' => 'Poprawne',
        'incorrect' => 'Niepoprawne',
        'correct_0' => 'Poziom 0',
        'correct_1' => 'Poziom 1',
        'correct_2' => 'Poziom 2',
        'correct_3' => 'Poziom 3',
        'correct_4' => 'Poziom 4',
        'correct_5' => 'Poziom 5',
        'correct_m' => 'Poziom +'
    ];

    protected $border = [
        'new' => 'rgb(52, 144, 220)',
        'repeat' => 'rgb(101, 116, 205)',
        'answers' => 'rgb(108, 117, 125)',
        'correct' => 'rgb(0, 193, 137)',
        'incorrect' => 'rgb(220, 53, 69)',
        'correct_0' => 'rgb(229, 233, 152)',
        'correct_1' => 'rgb(222, 234, 49)',
        'correct_2' => 'rgb(176, 220, 51)',
        'correct_3' => 'rgb(124, 204, 48)',
        'correct_4' => 'rgb(53, 174, 63)',
        'correct_5' => 'rgb(35, 105, 48)',
        'correct_m' => 'rgb(124, 71, 135)'
    ];

    protected $background = [
        'new' => 'rgba(52, 144, 220, 0.2)',
        'repeat' => 'rgba(101, 116, 205, 0.2)',
        'answers' => 'rgba(108, 117, 125, 0.2)',
        'correct' => 'rgba(0, 193, 137, 0.9)',
        'incorrect' => 'rgba(220, 53, 69, 0.9)',
        'correct_0' => 'rgba(229, 233, 152, 0.2)',
        'correct_1' => 'rgba(222, 234, 49, 0.2)',
        'correct_2' => 'rgba(176, 220, 51, 0.2)',
        'correct_3' => 'rgba(124, 204, 48, 0.2)',
        'correct_4' => 'rgba(53, 174, 63, 0.2)',
        'correct_5' => 'rgba(35, 105, 48, 0.2)',
        'correct_m' => 'rgba(124, 71, 135, 0.2)'
    ];

    public function summary(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $s = $request->input('questions_set_id');
        $f = UserStatQuestionRepeat::where('user_id',$h->user_id);
        if (!empty($s)) {
            $f->where('questions_set_id',$s);
            return response()->json(['stats'=>$f->first()]);
        }
        $tmp = $f->get();
        $keys = ['correct_0', 'correct_1', 'correct_2', 'correct_3', 'correct_4', 'correct_5', 'correct_m', 'correct_total', 'q_total', 'today_repeat'];
        $stats = [];
        foreach($keys as $k) {
            $stats[$k] = 0;
        }
        foreach($tmp as $r) {
            foreach($keys as $k) {
                $stats[$k] += $r->$k;
            }
        }
        $stats['user_id'] = $h->user_id;
        $stats['questions_set_id'] = 0;
        return response()->json(['stats'=>$stats]);
    }

    public function daily(Request $request)
    {
        Log::debug('first  : '.\Carbon\Carbon::now());
        $h = ApiHash::find($request->input('hash_id'));
        $sid = $request->input('questions_set_id');
        $settings = $request->input('settings');

        if(empty($settings['chart_start']) || empty($settings['chart_end'])){
            $temp  = UserStatDailyAggregateTotalSummary1::where('user_id',$h->user_id)->orderBy('date', 'asc')->get();
        }


        if(empty($settings['chart_start'])){
            Log::debug('first end : '.\Carbon\Carbon::now());
            if(!empty($temp)){
                $settings['chart_start'] = $temp[0]->date;
            }else{
                $settings['chart_start'] = \Carbon\Carbon::now()->format('d-m-Y');
            }
        }

        if(empty($settings['chart_end'])){
            Log::debug('second end  : '.\Carbon\Carbon::now());
            if(!empty($temp)){
                $settings['chart_end'] = $temp[count($temp)-1]->date;
            }else{
                $settings['chart_end'] = \Carbon\Carbon::now()->format('d-m-Y');
            }

            $chart_end_default = \Carbon\Carbon::parse($settings['chart_start'])->addDays(7)->format('Y-m-d');
            if($settings['chart_end'] < $chart_end_default ){
                $settings['chart_end'] = $chart_end_default;
            }
        }

        Log::debug('3  : '.\Carbon\Carbon::now());
        if ($settings['chart_group']=='day') {
            $f = (empty($sid)) ? UserStatDailyAggregateTotalSummary1::where('user_id',$h->user_id)->where('date', '>=', $settings['chart_start'])->where('date', '<=', $settings['chart_end'])->orderBy('date') : UserStatDailyAggregateSetSummary1::where('user_id',$h->user_id)->where('questions_set_id',$sid)->where('date', '>=', $settings['chart_start'])->where('date', '<=', $settings['chart_end'])->orderBy('date');
        } elseif($settings['chart_group']=='week') {
            $f = (empty($sid)) ? UserStatWeeklyAggregateTotalSummary1::where('user_id',$h->user_id)->where('date', '>=', $settings['chart_start'])->where('date', '<=', $settings['chart_end'])->orderBy('date') : UserStatWeeklyAggregateSetSummary1::where('user_id',$h->user_id)->where('questions_set_id',$sid)->where('date', '>=', $settings['chart_start'])->where('date', '<=', $settings['chart_end'])->orderBy('date');
        } elseif($settings['chart_group']=='month') {
            $f = (empty($sid)) ? UserStatMonthlyAggregateTotalSummary1::where('user_id',$h->user_id)->where('date', '>=', $settings['chart_start'])->where('date', '<=', $settings['chart_end'])->orderBy('date') : UserStatMonthlyAggregateSetSummary1::where('user_id',$h->user_id)->where('questions_set_id',$sid)->where('date', '>=', $settings['chart_start'])->where('date', '<=', $settings['chart_end'])->orderBy('date');
        }

        Log::debug('3 end  : '.\Carbon\Carbon::now());

        $query = (clone $f)->orderBy('date')->get();

        $dates = $this->processDailyGetDates($query);
        Log::debug('4 end  : '.\Carbon\Carbon::now());

        $stats = $this->processDaily($query, $settings);
        Log::debug('5 end  : '.\Carbon\Carbon::now());

        Log::debug('--------------');

        return response()->json([
          'chart_start' => $settings['chart_start'],
          'chart_end' => $settings['chart_end'],
          'dates' => $dates,
          'stats' => $stats,
        ]);
    }

    protected function processDailyGetDates($rows) {
        $dates = [];
        foreach($rows as $r) {
            $dates[] = $r->date;
        }
        return $dates;
    }

    protected function processDaily($batch, $settings) {
        $o = new \stdClass();
        $o->type = $settings['chart_type'];
        $o->data = new \stdClass();
        $o->data->labels = [$settings['chart_start']];
        $this->prepOptions($o,$settings);
        $this->prepDataSets($o,$settings);
        foreach($batch as $r) {
            $this->processDailyDatasets($o, $r, $settings);
        }

        $o->data->labels[] = $settings['chart_end'];
        return $o;
    }

    protected function prepOptions($o,$settings) {
        $o->options = new \stdClass();
        $o->options->scales = new \stdClass();
        $o->options->scales->xAxes = [];
        $x = new \stdClass();
        $x->type = 'time';
        $x->time = new \stdClass();
        $x->time->unit = $settings['chart_group'];
        $x->time->tooltipFormat = 'll';
        $x->stacked = true;
        $o->options->scales->xAxes[] = $x;

        $o->options->scales->yAxes = [];
        $y = new \stdClass();
        $y->stacked = true;
        $o->options->scales->yAxes[] = $y;

        $o->options->legend = new \stdClass();
        $o->options->legend->onHover = '';
        $o->options->legend->onLeave = '';
        $o->options->legend->display = false;

        $o->options->tooltips = new \stdClass();
//        $o->options->interaction->intersect = false;
        $o->options->tooltips->mode = 'index';
        $o->options->tooltips->intersect = false;
    }

    protected function prepDataSets($o, $settings) {
        $o->data->datasets = [];
        foreach($this->settings as $k => $t) {
            if ($settings[$k]) {
                $d = new \stdClass();
                $d->label = $t;
                $d->data = [];
                $d->backgroundColor = [];
                $d->backgroundColor[] = $this->background[$k];
                $d->borderColor = [];
                $d->borderColor[] = $this->border[$k];
                $d->borderWidth = 1;
                $o->data->datasets[] = $d;
            }
        }
    }

    protected function processDailyDatasets($o, $r, $settings) {
          if (($settings['chart_type']=='bar') && empty($o->data->labels)) {
              $multi = $settings['chart_group']=='day' ? 1 : ($settings['chart_group']=='week' ? 7 : 30 );
              $o->data->labels[] = date('Y-m-d',strtotime($r->date) - $multi * 24*60*60);
          }
          $o->data->labels[]=$r->date;
          $i=0;
          foreach(array_keys($this->settings) as $k) {
              if (!$settings[$k]) {
                  continue;
              }
              $e = new \stdClass();
              $e->t = $r->date;
              $e->y = $r->$k;
              $o->data->datasets[$i]->data[] = $e;
              if ($settings['chart_type']=='bar') {
                  $o->data->datasets[$i]->backgroundColor[] = $this->background[$k];
                  $o->data->datasets[$i]->borderColor[] = $this->border[$k];
              }
              $i++;
          }
    }
}
