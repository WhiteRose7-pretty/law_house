<?php

namespace App\Http\Controllers\App;

use App\ApiHash;
use App\LawDocumentElement;
use App\Package;
use App\PackageSet;
use App\User;
use App\UserPackage;
use App\UserPackageQuestionsSet;
use App\UserQuestion;
use App\UserQuestionAnswer;
use App\UserQuestionCalendar;
use App\UserQuestionRepeat;
use App\UserQuestionNew;
use App\UserQuestionKnown;
use App\UserTest;
use App\UserTestQuestion;
use App\QuestionsSet;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Type;

class ApiTestsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function answers(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $tn = $request->input('test');
        $f = $request->input('finish');
        $oot = $request->input('out_of_time');

        $test = UserTest::with('questions')->where('user_id', $h->user_id)->where('id', $tn['id'])->first();

        if (empty($test)) {
            return response()->json(['message' => 'Nie znaleziono testu'], 404);
        }

        if (!empty($test->finished_at)) {
            return response()->json(['message' => 'Test już zakończony, brak możliwości dalszej aktualizacji'], 409);
        }

        $can_update = empty($test['show_correct']); // czy możliwa jest aktualizacja, tylko jeśli to test, a nie nauka

        $a = [];
        $qids = [];
        foreach ($tn['questions'] as $q) {
            if (empty($q['answer'])) {
                continue;
            }
            $a[$q['id']] = $q['answer'];
            $qids[] = $q['id'];
        }
        if ($can_update) {
            $old_a = [];
            $aids = [];
            foreach ($test->questions as $q) {
                if (!empty($q->user_question_answer_id)) {
                    $aids[] = $q->user_question_answer_id;
                }
            }
            $ems[] = 'aids';
            $ems[] = $aids;
            if (!empty($aids)) {
                $tmp_a = UserQuestionAnswer::whereIn('id', $aids)->get();
                foreach ($tmp_a as $uqa) {
                    $old_a[$uqa->id] = $uqa->question_option_id;
                }
            }
            $ems[] = 'old_a';
            $ems[] = $old_a;
        }

        // return response()->json(['ems'=>$ems]);

        if (empty($a)) {
            if (empty($f)) {
                throw new Exception('Brak danych do aktualizacji');
            }
            $test->finished_at = date('Y-m-d H:i:s');
            if (!empty($test->time_limit) && empty($oot)) {
                $s1 = strtotime($test->created_at);
                $s2 = strtotime($test->finished_at);
                $test->completion_seconds = $s2 - $s1;
            }
            $test->save();
            return response()->json(['finished' => true, 'test' => $test]);
        }

        UserQuestionRepeat::batchReady($h->user_id, $qids, $test->is_repeat);
        $ems = [];
        // zakładamy, że nikomu nie chce się hackować i nie walidujemy correct
        DB::beginTransaction();
        $questions_answered_count = 0;
        $questions_answered_correct_count = 0;
        foreach ($test->questions as $q) {
            $ems[] = "LECE QID [$q->question_id]";
            if (empty($a[$q->question_id])) {
                $ems[] = "pusto QID [$q->question_id]";
                if (!empty($q->user_question_answer_id)) {
                    $questions_answered_count++;
                    $ems[] = 'policzyl odp juz zapisana';
                    if (!empty($q->correct)) {
                        $ems[] = 'policzyl odp juz zapisana jako CORRECT';
                        $questions_answered_correct_count++;
                    }
                }
                continue;
            }
            $ems[] = "jest QID [$q->question_id]";
            if (!empty($q->user_question_answer_id) && !$can_update) {
                $ems[] = 'policzyl odp juz zapisana (CANT UPDATE)';
                $questions_answered_count++;
                if (!empty($q->correct)) {
                    $ems[] = 'policzyl odp juz zapisana jako CORRECT (CANT UPDATE)';
                    $questions_answered_correct_count++;
                }
                continue;
            }
            $ems[] = 'policzyl nowa odp';
            $questions_answered_count++;
            if ($a[$q->question_id]['correct']) {
                $ems[] = 'policzyl nowa odp jako CORRECT';
                $questions_answered_correct_count++;
            }

            if (!empty($q->user_question_answer_id) && $old_a[$q->user_question_answer_id] == $a[$q->question_id]['question_option_id']) {
                $ems[] = 'pomija bo takie samo jak bylo';
                continue;
            }
            $uqa = new UserQuestionAnswer([
                'user_id' => $h->user_id,
                'question_id' => $q->question_id,
                'question_option_id' => $a[$q->question_id]['question_option_id'],
                'correct' => $a[$q->question_id]['correct'],
            ]);
            $uqa->save();
            $ems[] = "ZAPISAL ODP NA PYTANIE [$uqa->id]";
            $q->user_question_answer_id = $uqa->id;
            $q->correct = $a[$q->question_id]['correct'];
            $q->save();
            $ems[] = "ZAKTUALIZOWAL ODP W TESCIE [$uqa->id]";

            UserQuestionRepeat::batchOne($q->question_id, $a[$q->question_id]['correct']);
            $ems[] = "STRZELIL REPEATA [$q->question_id]";
        }
        $test->repeated_count = $test->repeated_count + UserQuestionRepeat::batchSave();
        $test->touch();
        $test->questions_answered_count = $questions_answered_count;
        $test->questions_answered_correct_count = $questions_answered_correct_count;


        $ems[] = "NOOOO???";
        if (!empty($f)) {
            $test->finished_at = date('Y-m-d H:i:s');
            if (!empty($test->time_limit) && empty($oot)) {
                $s1 = strtotime($test->created_at);
                $s2 = strtotime($test->finished_at);
                $test->completion_seconds = $s2 - $s1;
            }
            $ems[] = "zapis koniec";
            $test->save();
            DB::commit();
            return response()->json(['ems' => $ems, 'finished' => true, 'test' => $test]);
            return response()->json(['finished' => true, 'test' => $test]);
        }

        $test->save();
        // DB::rollback();
        DB::commit();
        $ems[] = "zapis";
        return response()->json(['ems' => $ems, 'test' => $test]);
        return response()->json([]);
    }

    public function continue(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        return response()->json(
            UserTest::where('user_id', $h->user_id)->whereNull('finished_at')->orderBy('updated_at', 'desc')->get()
        );
    }

    public function delete(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $id = $request->input('id');
        $test = UserTest::where('user_id', $h->user_id)->where('id', $id)->first();
        if (empty($test)) {
            return response()->json(['message' => 'Nie znaleziono testu'], 404);
        }
        $test->delete();
        return response()->json([]);
    }

    public function end(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $id = $request->input('id');
        $oot = $request->input('out_of_time');
        $test = UserTest::where('user_id', $h->user_id)->where('id', $id)->first();
        if (empty($test)) {
            return response()->json(['message' => 'Nie znaleziono testu'], 404);
        }
        if (!empty($test->finished_at)) {
            return response()->json(['message' => 'Test już zakończony'], 404);
        }
        $test->finished_at = date('Y-m-d H:i:s');
        if (!empty($test->time_limit) && empty($oot)) {
            $s1 = strtotime($test->created_at);
            $s2 = strtotime($test->finished_at);
            $test->completion_seconds = $s2 - $s1;
        }
        $test->save();
        return response()->json(['id' => $test->id]);
    }

    public function finished(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        return response()->json(
            UserTest::where('user_id', $h->user_id)->whereNotNull('finished_at')->orderBy('finished_at', 'desc')->get()
        );
    }

    public function calculate_questions1(Request $request)
    {
        $settings = $request->input('settings');
        $res = LawDocumentElement::getChildrenIds($settings['law_documents_elements_id'], []);
        return response()->json(['result' => $res, 'type' => gettype($res)]);
    }

    public static function get_known_questions($questions_ids, $user_id)
    {
        $questions = UserQuestionRepeat::where('user_id', '=', $user_id)->whereIn('id', $questions_ids);
        $all = [];
        foreach ($questions as $r) {
            $all[] = $r->id;
        }
        return $all;
    }

    public static function get_new_questions($questions_ids, $user_id)
    {
        $questions = ApiTestsController::get_known_questions($questions_ids, $user_id);
        return array_diff($questions_ids, $questions);
    }

    public function calculate_questions(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));

        $ids = UserPackageQuestionsSet::available_ids($h->user_id);
        $ids_ok = [];
        $ids_ok_names = [];
        $groups_ok = [];
        $groups_ok_names = [];
        $groups_partial = [];
        $groups_partial_names = [];
        $sets = $request->input('sets');
        $settings = $request->input('settings');
        foreach ($sets as $s) {
            if (!empty($s['group'])) {
                if ($s['selected']) {
                    $groups_ok[] = $s['index'];
                    $groups_ok_names[] = $s['group'];
                }
                foreach ($s['list'] as $ss) {
                    if (!in_array($ss['id'], $ids)) {
                        return response()->json(['message' => 'Nie masz dostępu do zestawu'], 404);
                    }
                    if ($ss['selected']) {
                        $ids_ok[] = $ss['id'];
                        $ids_ok_names[] = $ss['name'];
                        if (!in_array($s['index'], $groups_partial)) {
                            $groups_partial[] = $s['index'];
                            $groups_partial_names[] = $s['group'];
                        }
                    }
                }
                continue;
            }
            if (!in_array($s['id'], $ids)) {
                return response()->json(['message' => 'Nie masz dostępu do zestawu'], 404);
            }
            if ($s['selected']) {
                $ids_ok[] = $s['id'];
                $ids_ok_names[] = $s['name'];
                if (!in_array(0, $groups_partial)) {
                    $groups_partial[] = 0;
                    $groups_partial_names[] = '';
                }
            }
        }

        //get selected element from elements list
        $selected_laws = [];
        $elements = $request->input('elements');

        $res = static::add_selected_law($elements, [], $settings['set_date']);

        //questions for admin test
        $admin_tests = $request->input('admin_tests');
        //admin-test --> questions_string-> questions array -> merge
        $admin_tests_questions = [];
        if ($admin_tests) {
            foreach ($admin_tests as $admin_test) {
                if ($admin_test['selected']) {
                    $str_array = explode(',', $admin_test['questions']);
                    $int_array = array_map('intval', $str_array);
                    $admin_tests_questions = array_merge($admin_tests_questions, $int_array);
                }
            }
        }


        if (empty($ids_ok) && empty($admin_tests_questions)) {
            return response()->json(['message' => 'Brak wybranego zestawu'], 404);
        }

        $admin_tests_questions = array_unique($admin_tests_questions);

        if (!empty($settings['only_new'])) {
            $questions_ids = UserQuestionNew::available_ids($h->user_id, $ids_ok, $settings['limit'], $res, $settings['set_date']);
            $admin_tests_questions = ApiTestsController::get_known_questions($admin_tests_questions, $h->user_id);
        } elseif (!empty($settings['only_known'])) {
            $questions_ids = UserQuestionKnown::available_ids($h->user_id, $ids_ok, $settings['limit'], $res, $settings['set_date']);
            $admin_tests_questions = ApiTestsController::get_new_questions($admin_tests_questions, $h->user_id);
        } else {
            $questions_ids = UserQuestion::available_ids($h->user_id, $ids_ok, $settings['limit'], $res, $settings['set_date']);
        }

        $limit = $settings['limit'];
        $questions_ids = array_merge($questions_ids, $admin_tests_questions);

        if((!empty($limit)) && ($limit < count($questions_ids))){
            $questions_ids = array_slice($questions_ids, 0, $limit);
        }

        shuffle($questions_ids);
        return response()->json(['question_counts' => count($questions_ids), 'questions' => $questions_ids, 'admin_questions' => $admin_tests_questions, 'raw' => $res]);

    }

    public static function add_selected_law($elements, $selected_elements, $set_date = null)
    {
        foreach ($elements as $element) {
            if (count($element['children']) > 0) {
                $selected_elements = self::add_selected_law($element['children'], $selected_elements, $set_date);
            } else if ($element['selected']) {
                $selected_elements[] = $element['data']['id'];
                //date check
                $selected_elements = LawDocumentElement::getChildrenIds($element['data']['id'], $selected_elements, $set_date);
            }
        }
        return $selected_elements;
    }

    public function new(Request $request)
    {
        Log::debug('database read end1: ' . Carbon::now()->toDateTimeString());
        $h = ApiHash::find($request->input('hash_id'));
        $ids = UserPackageQuestionsSet::available_ids($h->user_id);
        $ids_ok = [];
        $ids_ok_names = [];
        $groups_ok = [];
        $groups_ok_names = [];
        $groups_partial = [];
        $groups_partial_names = [];
        $sets = $request->input('sets');
        $settings = $request->input('settings');
        $elements = $request->input('elements');

        foreach ($sets as $s) {
            if (!empty($s['group'])) {
                if ($s['selected']) {
                    $groups_ok[] = $s['index'];
                    $groups_ok_names[] = $s['group'];
                }
                foreach ($s['list'] as $ss) {
                    if (!in_array($ss['id'], $ids)) {
                        return response()->json(['message' => 'Nie masz dostępu do zestawu'], 404);
                    }
                    if ($ss['selected']) {
                        $ids_ok[] = $ss['id'];
                        $ids_ok_names[] = $ss['name'];
                        if (!in_array($s['index'], $groups_partial)) {
                            $groups_partial[] = $s['index'];
                            $groups_partial_names[] = $s['group'];
                        }
                    }
                }
                continue;
            }
            if (!in_array($s['id'], $ids)) {
                return response()->json(['message' => 'Nie masz dostępu do zestawu'], 404);
            }
            if ($s['selected']) {
                $ids_ok[] = $s['id'];
                $ids_ok_names[] = $s['name'];
                if (!in_array(0, $groups_partial)) {
                    $groups_partial[] = 0;
                    $groups_partial_names[] = '';
                }
            }
        }

        //questions for admin test
        $admin_tests = $request->input('admin_tests');
        //admin-test --> questions_string-> questions array -> merge
        $admin_tests_questions = [];
        foreach ($admin_tests as $admin_test) {
            if ($admin_test['selected']) {
                $str_array = explode(',', $admin_test['questions']);
                $int_array = array_map('intval', $str_array);
                $admin_tests_questions = array_merge($admin_tests_questions, $int_array);
            }
        }
        $admin_tests_questions = array_unique($admin_tests_questions);


        if (empty($ids_ok) && empty($admin_tests_questions)) {
            return response()->json(['message' => 'Brak wybranego zestawu'], 404);
        }


        $info = true;
        if (count($ids_ok) == 1) {
            $name = $ids_ok_names[0];
            $info = '';
        } elseif (count($groups_ok) && !in_array(0, $groups_partial) && $groups_ok === $groups_partial) {
            if (count($groups_ok) > 1) {
                $name = 'Test Mieszany';
            } else {
                $name = $groups_ok_names[0];
                $info = '';
            }
        } elseif (!in_array(0, $groups_partial) && count($groups_partial) == 1) {
            $name = $groups_partial_names[0];
        } else {
            $name = 'Test Mieszany';
        }
        if (!empty($info)) {
            $info = join(', ', $ids_ok_names);
        }


        Log::debug('database read end3: ' . Carbon::now()->toDateTimeString());
        $res = static::add_selected_law($elements, [], $settings['set_date']);
        Log::debug('database read end4: ' . Carbon::now()->toDateTimeString());

        if (!empty($settings['only_new'])) {
            $questions_ids = UserQuestionNew::available_ids($h->user_id, $ids_ok, $settings['limit'], $res, $settings['set_date']);
            $admin_tests_questions = ApiTestsController::get_new_questions($admin_tests_questions, $h->user_id);

        } elseif (!empty($settings['only_known'])) {
            $questions_ids = UserQuestionKnown::available_ids($h->user_id, $ids_ok, $settings['limit'], $res, $settings['set_date']);
            $admin_tests_questions = ApiTestsController::get_known_questions($admin_tests_questions, $h->user_id);
        } else {
            $questions_ids = UserQuestion::available_ids($h->user_id, $ids_ok, $settings['limit'], $res, $settings['set_date']);
        }

        $limit = $settings['limit'];
        $questions_ids = array_merge($questions_ids, $admin_tests_questions);


        if((!empty($limit)) && ($limit < count($questions_ids))){
            $questions_ids = array_slice($questions_ids, 0, $limit);
        }

        shuffle($questions_ids);

        Log::debug('database read end5: ' . Carbon::now()->toDateTimeString());

        $ut = DB::transaction(function () use ($questions_ids, $name, $info, $settings, $h) {
            $ut = new UserTest();
            $ut->user_id = $h->user_id;
            $ut->name = $name;
            $ut->info = $info;
            $ut->time_limit = $settings['time'];
            $ut->show_correct = $settings['show_correct'];
            $ut->questions_count = count($questions_ids);

            $ut->save();
            foreach ($questions_ids as $qid) {
                $utq = new UserTestQuestion();
                $utq->user_test_id = $ut->id;
                $utq->question_id = $qid;
                $utq->save();
            }
            return $ut;
        });

        Log::debug('database read end6: ' . Carbon::now()->toDateTimeString());
        return response()->json(['user_test_id' => $ut->id]);
    }

    public function repeat(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $questions_sets_ids = $request->input('ids');
        $date = $request->input('date');
        $name = $request->input('name');
        $questions_sets_ids = empty($questions_sets_ids) ? [] : (is_array($questions_sets_ids) ? $questions_sets_ids : [$questions_sets_ids]);
        sort($questions_sets_ids);
        $date = empty($date) ? date('Y-m-d') : $date;

        $test = UserTest::where('user_id', $h->user_id)->where('is_repeat', 1)->where('repeat_sets_ids', implode(',', $questions_sets_ids))->where('repeat_date', $date)->whereNull('finished_at')->first();

        if (!empty($test)) {
            return response()->json(['id' => $test->id]);
        }

        $questions_ids = UserQuestionCalendar::planned($h->user_id, $date, $questions_sets_ids);

        if (empty($questions_ids)) {
            return response()->json(['message' => 'Brak pytań do powtórki'], 409);
        }

        $ut = DB::transaction(function () use ($questions_ids, $name, $questions_sets_ids, $date, $h) {
            $ut = new UserTest();
            $ut->user_id = $h->user_id;
            $ut->name = $name;
            $ut->info = '';
            $ut->show_correct = 1;
            $ut->is_repeat = 1;
            $ut->repeat_date = $date;
            $ut->repeat_sets_ids = implode(',', $questions_sets_ids);
            $ut->questions_count = count($questions_ids);
            $ut->save();
            foreach ($questions_ids as $qid) {
                $utq = new UserTestQuestion();
                $utq->user_test_id = $ut->id;
                $utq->question_id = $qid;
                $utq->save();
            }
            return $ut;
        });

        return response()->json(['id' => $ut->id]);
    }

    public function run(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $id = $request->input('id');
        $test = UserTest::where('user_id', $h->user_id)->where('id', $id)->first();
        if (empty($test)) {
            return response()->json(['message' => 'Nie znaleziono testu'], 404);
        }
        if (!empty($test->finished_at)) {
            return response()->json($test);
        }
        $test->questions = UserTestQuestion::unanswered($test, $test->is_repeat);
        return response()->json($test);
    }

    public function summary(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $f = $request->input('filter');
        $id = $request->input('id');
        $test = UserTest::where('user_id', $h->user_id)->where('id', $id)->first();
        if (empty($test) || empty($test->finished_at)) {
            return response()->json(['message' => 'Nie znaleziono testu'], 404);
        }
        if ($f == 'noquestions') {
            $test->questions = [];
        } else {
            $test->questions = UserTestQuestion::full($test, $f);
        }
        $today = date('Y-m-d') == date('Y-m-d', strtotime($test->finished_at)) ? true : false;
        return response()->json(['test' => $test, 'today' => $today]);
    }
}
