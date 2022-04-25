<?php

namespace App\Http\Controllers\Admin;

use App\Question;
use App\QuestionOption;
use App\QuestionsSet;
use App\QuestionLaw;
use App\LawDocument;
use App\LawDocumentElement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


class ApiQuestionsController extends Controller
{
    public function repeat_enable(Request $request)
    {
        $id = $request->input('id');
        $set_of_questions = QuestionsSet::find($id);
        $set_of_questions->repeatable = $request->input('repeatable');
        $set_of_questions->save();

        return response()->json([
            'id' => $id,
            'repeatable' => $set_of_questions->repeatable,
        ]);

    }

    public static function checkAll()
    {
        try {
            $current_time = \Carbon\Carbon::now();
            $legals = LawDocumentElement::all();
            foreach ($legals as $legal) {
                $legal->after_date = static::checkLegal_End($legal, $current_time);
                $legal->before_date = static::checkLegal_Start($legal, $current_time);
                $legal->out_date = $legal->after_date | $legal->before_date;
                $legal->save();
                if($legal->after_date){
                    $lde = $legal;
                    $pei = $lde->parent_element_id;
                    $ldi = $lde->law_document_id;
                    $p = $lde->position;
                    $lde_id = $legal->id;

                    ApiLawController::update_question_old($lde_id, true);

                    $lde->delete();
                    LawDocument::clearCache();
                    if (empty($pei)) {
                        LawDocumentElement::where('law_document_id',$ldi)->whereNull('parent_element_id')->where('position','>=',$p)->update(['position'=>DB::raw('position-1')]);
                    } else {
                        LawDocumentElement::where('law_document_id',$ldi)->where('parent_element_id',$pei)->where('position','>=',$p)->update(['position'=>DB::raw('position-1')]);
                    }
                    ApiLawController::update_question_old($lde_id,true );
                }

            }

            $query_sets = QuestionsSet::withCount(['questions' => function (Builder $query) {
                $query->where('deleted', '=', '0');
            },])->get();
            $querys = Question::with(['options', 'laws'])->withCount('laws')->get();
            foreach ($querys as $query) {
                $query->after_date = static::checkAfterDate($query, $current_time);
                $query->before_date = static::checkBeforeDate($query, $current_time);
                $query->out_date = $query->after_date | $query->before_date;
                $query->save();
            }

            foreach ($query_sets as $query_set) {
                $query_set->after_date = static::checkAfterDate_QuestionSet($query_set, $current_time);
                $query_set->before_date = static::checkBeforeDate_QuestionSet($query_set, $current_time);
                $query_set->out_date = $query_set->after_date | $query_set->before_date;
                $query_set->save();
            }

        } catch (\Exception $e) {
            report($e);
            echo "dd";
            echo $e;
            return false;
        }

        echo "end";

    }

    public static function checkLegal_Start($legal, $current_time)
    {
        if ($legal->start_at && ($current_time->diffInRealSeconds($legal->start_at, false) > 0)) {
            return true;
        }
        return false;
    }

    public static function checkLegal_End($legal, $current_time)
    {
        if ($legal->end_at && ($current_time->diffInRealSeconds($legal->end_at, false) < -86400)) {
            return true;
        }
        return false;
    }

    public function questions(Request $request)
    {
//        static::checkAll();
        $id = $request->input('questions_set_id');
        $l = $request->input('limit');
        $q = $request->input('query');
        $p = $request->input('page');
        $legal_element_id = $request->input('legal_element_id');

        $query = Question::where('deleted', '=', '0')->with(['options', 'laws', 'questions_set'])->withCount('laws');
        if (empty($id)) {
        } else {
            $query = $query->where('questions_set_id', $id);
        }

        if (empty($q)) {
        } else {
            $query = $query->where(function ($query1) use ($q) {
                $query1->where('question', 'like', '%' . $q . '%')
                    ->orWhere('id', $q);
            });
        }

        if (empty($legal_element_id)) {
        } else {
            $query = $query->whereHas('laws', function ($query) use ($legal_element_id) {
                $query->where('law_document_element_id', '=', $legal_element_id);
            });
        }

        $query = $query->orderBy('old_status', 'desc')
            ->orderBy('out_date', 'desc')->orderBy('id', 'asc');
        $count = $query->count();
        $pages = ceil($count / $l);
        $page = $p > $pages ? $pages : $p;
        $skip = ($page - 1) * $l;
        $sql = $query->toSql();
        $results = $query->skip($skip)->take($l)->get();

        return response()->json([
            'page' => $page,
            'pages' => $pages,
            'count' => $count,
            'results' => $results,
            'sql' => $sql,
        ]);
    }

    public static function checkOutDate($question, $current_time)
    {
        $current_time = \Carbon\Carbon::now();
        $legal_basis = $question->laws;
        foreach ($legal_basis as $item) {
            if ($current_time->diffInRealSeconds($item->law_document_element->end_at, false) < -86400) {
                return true;
            }
            if ($current_time->diffInRealSeconds($item->law_document_element->start_at, false) > 0) {
                return true;
            }
        }
        return false;
    }


    public static function checkQuestionValidate($question)
    {
        $current_time = \Carbon\Carbon::now();
        if ($current_time->diffInRealSeconds($question->end_date, false) < -86400) {
            return true;
        }
        if ($current_time->diffInRealSeconds($question->start_date, false) > 0) {
            return true;
        }
        return false;
    }

    public static function checkAfterDate($question, $current_time)
    {
        $current_time = \Carbon\Carbon::now();
        if (static::checkQuestionValidate($question)) {
            return false;
        }
        $legal_basis = $question->laws;
        foreach ($legal_basis as $item) {
            if (static::checkQuestionValidate($item->law_document_element)) {
                return false;
            }
            if ($current_time->diffInRealSeconds($item->law_document_element->end_at, false) < -86400) {
                return true;
            }
        }
        return false;
    }

    public static function checkBeforeDate($question, $current_time)
    {
        $current_time = \Carbon\Carbon::now();

        $legal_basis = $question->laws;
        foreach ($legal_basis as $item) {
            if (static::checkQuestionValidate($item->law_document_element)) {
                return false;
            }
            if ($current_time->diffInRealSeconds($item->law_document_element->start_at, false) > 0) {
                return true;
            }
        }
        return false;
    }


    public function questionsSetsList(Request $request)
    {
        $current_time = \Carbon\Carbon::now();
        $l = $request->input('limit');
        $q = $request->input('query');
        $p = $request->input('page');

        if (empty($q)) {
            $count = QuestionsSet::count();
            $pages = ceil($count / $l);
            $page = $p > $pages ? $pages : $p;
            $skip = ($page - 1) * $l;
            $results = QuestionsSet::withCount(['questions' => function (Builder $query) {
                $query->where('deleted', '=', '0');
            },])->skip($skip)->take($l)->get();
        } else {
            $count = QuestionsSet::where('name', 'like', $q . '%')->orWhere('id', $q)->orwhereHas('questions', function ($query) use ($q) {
                    $query->where('id', '=' ,$q);
                })->count();
            $pages = ceil($count / $l);
            $page = $p > $pages ? $pages : $p;
            $skip = ($page - 1) * $l;
            $results = QuestionsSet::withCount(['questions' => function (Builder $query) {
                $query->where('deleted', '=', '0');
            },])->where('name', 'like', $q . '%')->orWhere('id', $q)->orwhereHas('questions', function ($query) use ($q) {
                $query->where('id', '=' ,$q);
            })->skip($skip)->take($l)->get();
        }

        return response()->json([
            'page' => $page,
            'pages' => $pages,
            'count' => $count,
            'results' => $results,
        ]);
    }

    public static function checkOutDate_QuestionSet($question_set, $current_time)
    {
        $current_time = \Carbon\Carbon::now();
        $questions = $question_set->questions;
        foreach ($questions as $item) {
            if ($item->out_date) {
                return true;
            }
        }
        return false;
    }

    public static function checkBeforeDate_QuestionSet($question_set, $current_time)
    {
        $current_time = \Carbon\Carbon::now();
        $questions = Question::where('questions_set_id', '=', $question_set->id)->where('deleted', '=', '0')->get();

        foreach ($questions as $item) {
            if ($item->before_date) {
                return true;
            }
        }
        return false;
    }

    public static function checkAfterDate_QuestionSet($question_set, $current_time)
    {
        $current_time = \Carbon\Carbon::now();
        $questions = Question::where('questions_set_id', '=', $question_set->id)->where('deleted', '=', '0')->get();

        foreach ($questions as $item) {
            if ($item->after_date) {
                return true;
            }
        }
        return false;
    }

    public function questionUpdate(Request $request)
    {
        $qn = $request->input('question');
        $qsid = $request->input('questions_set_id');
        $qs = QuestionsSet::find($qsid);
        if (empty($qs)) {
            return response()->json(['message' => 'Questions set not found with the given ID'], 404);
        }
        $legal_basis_generated = null;
        if (!empty($qn['laws_count'])) {
            $legal_basis_generated = '';
            $lawdocids = [];
            for ($i = 0; $i < $qn['laws_count']; $i++) {
                if (empty($qn['laws'][$i]['law_document_id']) || empty($qn['laws'][$i]['law_document_element_id'])) {
                    return response()->json(['message' => 'Ustal wszystkie elementy podstawy prawnej lub zaktualizuj ich ilość'], 404);
                }
                $lawdocids[$qn['laws'][$i]['law_document_id']] = $qn['laws'][$i]['law_document_id'];
            }
            $listLaws = LawDocument::listAllWithElementsAndContent($lawdocids, true);
            for ($i = 0; $i < $qn['laws_count']; $i++) {
                $legal_basis_generated .= '<div><strong>';
                $legal_basis_generated .= $listLaws[$qn['laws'][$i]['law_document_id']]['label'];
                $legal_basis_generated .= ', ' . $listLaws[$qn['laws'][$i]['law_document_id']]['elements'][$qn['laws'][$i]['law_document_element_id']]['label'] . '</strong><p>';
                $legal_basis_generated .= $listLaws[$qn['laws'][$i]['law_document_id']]['elements'][$qn['laws'][$i]['law_document_element_id']]['content'];
                $legal_basis_generated .= '</p></div>';
            }
        }
        if (empty($qn['id'])) {
            DB::beginTransaction();
            $q = new Question();
            $q->question = $qn['question'];
            $q->questions_set_id = $qsid;
            if (empty($qn['laws_count'])) {
                $q->legal_basis_text = $qn['legal_basis_text'];
                $q->legal_basis_generated = null;
            } else {
                $q->legal_basis_text = null;
                $q->legal_basis_generated = $legal_basis_generated;
            }

            $q->save();
            $qo1 = new QuestionOption();
            $qo1->option = $qn['options'][0]['option'];
            $qo1->question_id = $q->id;
            $qo1->correct = !empty($qn['options'][0]['correct']);
            $qo1->save();
            $qo2 = new QuestionOption();
            $qo2->option = $qn['options'][1]['option'];
            $qo2->question_id = $q->id;
            $qo2->correct = !empty($qn['options'][1]['correct']);
            $qo2->save();
            $qo3 = new QuestionOption();
            $qo3->option = $qn['options'][2]['option'];
            $qo3->question_id = $q->id;
            $qo3->correct = !empty($qn['options'][2]['correct']);
            $qo3->save();
            for ($i = 0; $i < $qn['laws_count']; $i++) {
                $ql = new QuestionLaw();
                $ql->question_id = $q->id;
                $ql->law_document_id = $qn['laws'][$i]['law_document_id'];
                $ql->law_document_element_id = $qn['laws'][$i]['law_document_element_id'];
                $ql->save();

            }
            $qs->touch();
            DB::commit();
            return response()->json();

        }
        $q = Question::find($qn['id']);
        $qo1 = QuestionOption::find($qn['options'][0]['id']);
        $qo2 = QuestionOption::find($qn['options'][1]['id']);
        $qo3 = QuestionOption::find($qn['options'][2]['id']);
        if (empty($q) || empty($qo1) || empty($qo2) || empty($qo3)) {
            return response()->json(['message' => 'Question not found with the given ID'], 404);
        }
        DB::beginTransaction();
        if (empty($qn['laws_count'])) {
            $q->legal_basis_text = $qn['legal_basis_text'];
            $q->legal_basis_generated = null;
        } else {
            $q->legal_basis_text = null;
            $q->legal_basis_generated = $legal_basis_generated;
        }
        $q->question = $qn['question'];
        $q->old_status = false;

        $q->save();


        $qo1->option = $qn['options'][0]['option'];
        $qo1->correct = !empty($qn['options'][0]['correct']);
        $qo1->save();
        $qo2->option = $qn['options'][1]['option'];
        $qo2->correct = !empty($qn['options'][1]['correct']);
        $qo2->save();
        $qo3->option = $qn['options'][2]['option'];
        $qo3->correct = !empty($qn['options'][2]['correct']);
        $qo3->save();
        QuestionLaw::where('question_id', $q->id)->delete();
        for ($i = 0; $i < $qn['laws_count']; $i++) {
            $ql = new QuestionLaw();
            $ql->question_id = $q->id;
            $ql->law_document_id = $qn['laws'][$i]['law_document_id'];
            $ql->law_document_element_id = $qn['laws'][$i]['law_document_element_id'];
            $ql->start_date = $qn['laws'][$i]['start_date'];
            $ql->end_date = $qn['laws'][$i]['end_date'];
            $ql->save();
        }

        $qs->touch();

        $query_outdate = Question::with(['options', 'laws'])->withCount('laws')->where('id', '=', $q->id)->get();

        # question outdate check

        foreach ($query_outdate as $item) {
            $item->after_date = static::checkAfterDate($item, '');
            $item->before_date = static::checkBeforeDate($item, '');
            $item->out_date = $item->after_date | $item->before_date;
            $item->save();
        }
        $q->help_text = $qn['help_text'];

        $q->save();

        $query_sets = QuestionsSet::withCount(['questions' => function (Builder $query) {
            $query->where('deleted', '=', '0');
        },])->where('id', $qn['questions_set_id'])->get();

        foreach ($query_sets as $query_set) {
            $query_set->after_date = static::checkAfterDate_QuestionSet($query_set, '');
            $query_set->before_date = static::checkBeforeDate_QuestionSet($query_set, '');
            $query_set->out_date = $query_set->after_date | $query_set->before_date;
            $query_set->old_status = static::checkOldStatus_QuestionSet($query_set);
            $query_set->save();
        }

        DB::commit();

        return response()->json(['question' => $q]);
    }

    public static function checkOldStatus_QuestionSet($question_set)
    {
        $questions = Question::where('questions_set_id', '=', $question_set->id)->where('deleted', '=', '0')->get();
        foreach ($questions as $item) {
            if ($item->old_status) {
                return true;
            }
        }
        return false;
    }

    public function questionRemove(Request $request)
    {
        $q = Question::with(['user_question_answers',
            'users_game_participants_answers',
            'users_game_questions',
            'users_tests_questions',
            'users_questions_repeats',
            'users_stats_daily_aggregates',])->find($request->input('id'));
        if (empty($q)) {
            return response()->json(['message' => 'Question not found with the given ID'], 404);
        }
        $qs = QuestionsSet::find($q->questions_set_id);

        foreach ($q->users_questions_repeats as $repeat) {
            $repeat->delete();
        }

        foreach ($q->users_stats_daily_aggregates as $aggregate) {
            $aggregate->delete();
        }


        $q->deleted = true;
        $q->save();
//        try {
//            $q->delete();
//        } catch(\Exception $e) {
//            return response()->json(['message'=>'Nie można usunąć tego pytania, ktoś już na nie odpowiadał'],409);
//        }
        $qs->touch();

        $query_sets = QuestionsSet::withCount(['questions' => function (Builder $query) {
            $query->where('deleted', '=', '0');
        },])->where('id', $q->questions_set_id)->get();

        foreach ($query_sets as $query_set) {
            $query_set->after_date = static::checkAfterDate_QuestionSet($query_set, '');
            $query_set->before_date = static::checkBeforeDate_QuestionSet($query_set, '');
            $query_set->out_date = $query_set->after_date | $query_set->before_date;
            $query_set->old_status = static::checkOldStatus_QuestionSet($query_set);
            $query_set->save();
        }

        return response()->json([]);
    }

    public function questionsSet(Request $request)
    {
        $qs = QuestionsSet::find($request->input('id'));
        if (empty($qs)) {
            return response()->json(['message' => 'Questions set not found with the given ID'], 404);
        }
        return response()->json($qs);
    }

    public function questionsSetsList2(Request $request)
    {
        $l = $request->input('limit');
        $q = $request->input('query');
        $p = $request->input('page');

        if (empty($q)) {
            $count = QuestionsSet::count();
            $pages = ceil($count / $l);
            $page = $p > $pages ? $pages : $p;
            $skip = ($page - 1) * $l;
            $results = QuestionsSet::withCount(['questions' => function (Builder $query) {
                $query->where('deleted', '=', '0');
            },])->skip($skip)->take($l)->get();
            return response()->json([
                'page' => $page,
                'pages' => $pages,
                'count' => $count,
                'results' => $results,
            ]);
        }

        $count = QuestionsSet::where('name', 'like', $q . '%')->count();
        $pages = ceil($count / $l);
        $page = $p > $pages ? $pages : $p;
        $skip = ($page - 1) * $l;
        $results = QuestionsSet::withCount(['questions' => function (Builder $query) {
            $query->where('deleted', '=', '0');
        },])->where('name', 'like', $q . '%')->skip($skip)->take($l)->get();
        return response()->json([
            'page' => $page,
            'pages' => $pages,
            'count' => $count,
            'results' => $results,
        ]);
    }

    public function questionsSetsListAll(Request $request)
    {
        $count = QuestionsSet::count();
        $tmp = QuestionsSet::get();
        $results = [];
        foreach ($tmp as $qs) {
            $results[$qs->id] = $qs;
        }
        return response()->json([
            'count' => $count,
            'results' => $results,
        ]);
    }

    public function questionsSetsRemove(Request $request)
    {
        $qs = QuestionsSet::find($request->input('id'));
        if (empty($qs)) {
            return response()->json([], 404);
        }
        $qs->delete();
        return response()->json([]);
    }

    public function questionsSetsUpdate(Request $request)
    {
        $qsn = (object)$request->input('set');
        if (empty($qsn->id)) {
            $qs = new QuestionsSet();
            $qs->name = $qsn->name;
            $qs->group = $qsn->group;
            $qs->save();
            return response()->json($qs);
        }

        $qs = QuestionsSet::find($qsn->id);
        if (empty($qs)) {
            return response()->json(['message' => 'Questions set not found with the given ID'], 404);
        }

        $qs->name = $qsn->name;
        $qs->group = $qsn->group;
        $qs->save();
        return response()->json($qs);
    }
}
