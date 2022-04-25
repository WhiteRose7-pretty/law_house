<?php

namespace App\Http\Controllers\Admin;

use App\Content;
use App\Http\Controllers\Admin\ApiQuestionsController;
use App\LawDocument;
use App\LawDocumentElement;
use App\Http\Controllers\Controller;
use App\Question;
use App\QuestionsSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use function GuzzleHttp\Psr7\_caseless_remove;


class ApiLawController extends Controller
{
    protected $children = [];
    protected $parents = [];
    protected $elements = [];
    protected $opened = [];

    public static function update_question_old($legal_id, $legal_status)
    {
        $querys = Question::with(['options', 'laws'])->withCount('laws')->whereHas('laws', function ($query) use ($legal_id) {
            $query->where('law_document_element_id', '=', $legal_id);
        })->get();

        foreach ($querys as $item) {
            if ($legal_status) {
                $item->old_status = true;
            }
            $item->after_date = ApiQuestionsController::checkAfterDate($item, '');
            $item->before_date = ApiQuestionsController::checkBeforeDate($item, '');
            $item->out_date = $item->after_date | $item->before_date;
            $item->save();

            $query_sets = QuestionsSet::withCount(['questions' => function (Builder $query) {
                $query->where('deleted', '=', '0');
            },])->where('id', $item->questions_set_id)->get();

            foreach ($query_sets as $query_set) {
                $query_set->after_date = ApiQuestionsController::checkAfterDate_QuestionSet($query_set, '');
                $query_set->before_date = ApiQuestionsController::checkBeforeDate_QuestionSet($query_set, '');
                $query_set->out_date = $query_set->after_date | $query_set->before_date;
                if ($legal_status) {
                    $query_set->old_status = $legal_status;
                }

                $query_set->save();
            }
        }
    }

    public function document(Request $request)
    {
        $ld = LawDocument::find($request->input('id'));
        if (empty($ld)) {
            return response()->json(['message' => 'Content not found with the given ID'], 404);
        }
        return response()->json($ld);
    }

    public function download(Request $request)
    {
        //get parameter
        $file_name = 'public/' . $request->input('title') . '.txt';
        Storage::put($file_name, $request->input('text'));
        $file_url = Storage::url($file_name);
        return response()->json($file_url);

    }

    public function documentsList(Request $request)
    {
        $l = $request->input('limit');
        $q = $request->input('query');
        $p = $request->input('page');

        if (empty($q)) {
            $ldount = LawDocument::count();
            $pages = ceil($ldount / $l);
            $page = $p > $pages ? $pages : $p;
            $skip = ($page - 1) * $l;
            $results = LawDocument::select('id', 'updated_at', 'name', 'identifier', 'signed_at', 'info')->skip($skip)->take($l)->get();

            $index  = 1;
            foreach($results as $item){
                if($index > 3){
                    $item->audio = true;
                }
                else{
                    $item->audio = false;
                }
                $index = $index + 1;
            }

            return response()->json([
                'page' => $page,
                'pages' => $pages,
                'count' => $ldount,
                'results' => $results,
            ]);
        }

        $ldount = LawDocument::where('name', 'like', $q . '%')->orWhere('identifier', 'like', $q . '%')->count();
        $pages = ceil($ldount / $l);
        $page = $p > $pages ? $pages : $p;
        $skip = ($page - 1) * $l;
        $results = LawDocument::select('id', 'updated_at', 'name', 'identifier', 'signed_at', 'info')->where('name', 'like', $q . '%')->orWhere('identifier', 'like', $q . '%')->skip($skip)->take($l)->get();

        foreach($results as $item){
            $item->audio = true;
        }

        return response()->json([
            'page' => $page,
            'pages' => $pages,
            'count' => $ldount,
            'results' => $results,
        ]);
    }

    public function documentsListAll()
    {
        return response()->json([
            'results' => LawDocument::listAllWithElementsAndContent(),
        ]);
    }

    public function documentsListAllChapter()
    {
        return response()->json([
            'results' => LawDocument::listAllWithElementsAndContentChapter(),
        ]);
    }

    public function documentRemove(Request $request)
    {
        $ld = LawDocument::find($request->input('id'));
        if (empty($ld)) {
            return response()->json([], 404);
        }
        $ld->delete();
        LawDocument::clearCache();
        return response()->json([]);
    }

    public function documentUpdate(Request $request)
    {
        $ldn = (object)$request->input('document');
        if (empty($ldn->id)) {
            $ld = new LawDocument();
            $ld->name = $ldn->name;
            $ld->identifier = $ldn->identifier;
            $ld->signed_at = $ldn->signed_at;
            $ld->info = $ldn->info;
            $ld->save();
            LawDocument::clearCache();
            return response()->json($ld);
        }

        $ld = LawDocument::find($ldn->id);
        if (empty($ld)) {
            return response()->json(['message' => 'Document not found with the given ID'], 404);
        }

        $ld->name = $ldn->name;
        $ld->identifier = $ldn->identifier;
        $ld->signed_at = $ldn->signed_at;
        $ld->info = $ldn->info;
        $ld->save();
        LawDocument::clearCache();
        return response()->json($ld);
    }

    public function elements(Request $request)
    {
        $ldes = LawDocumentElement::where('law_document_id', $request->input('id'))->orderBy('depth', 'asc')->orderBy('position', 'asc')->get();

        $elements = (array)$request->input('elements');

        $this->processOpened($elements);

        $this->processElements($ldes);

        return response()->json(['elements' => $this->prepareJson()]);
    }

    public function elements_chapter(Request $request)
    {
        Log::debug('element chapter1');
        $id = $request->input('id');
        $chapter_name_list_not = ['Artykuł', 'Art.', 'art.', 'ust.', '§', 'pkt', 'lit.', 'ppkt', '-', 'nr', 'Punkt', 'Litera', 'i', 'ii', 'iii', 'Preambuła'];
        $documents = LawDocument::limit($id)->get();
        $res = [];
        foreach ($documents as $document) {
            $ldes = LawDocumentElement::where('law_document_id', $document->id)->whereNotIn('name', $chapter_name_list_not)->orderBy('depth', 'asc')->orderBy('position', 'asc')->get();
            $elements = (array)$request->input('elements');
            $this->processOpened($elements);
            $this->processElements($ldes);
            $expended_elements = $this->prepareJson(false);
            $document->selected = false;
            $document->children = $expended_elements;
            $document->type = 'doc';
            $document->text = $document->name;
            $document->opened = false;
            $res[] = $document;
            $this->elements = [];
            $this->children = [];
            $this->parents = [];
            $this->opened = [];
        }
        return response()->json(['elements' => $res]);
    }


    protected function processOpened($elements)
    {
        foreach ($elements as $e) {
            if (!empty($e['opened'])) {
                $this->opened[$e['data']['id']] = true;
            }
            if (!empty($e['children'])) {
                $this->processOpened($e['children']);
            }
        }
    }

    protected function processElements($ldes)
    {
        if (empty($ldes)) {
            return;
        }

        foreach ($ldes as $e) {
            $this->elements[$e->id] = $this->elementForJson($e);

            if (!empty($e->parent_element_id)) {
                if (empty($this->children[$e->parent_element_id])) {
                    $this->children[$e->parent_element_id] = [];
                }
                $this->children[$e->parent_element_id][] = $e->id;
                continue;
            }
            if (empty($this->children[0])) {
                $this->children[0] = [];
            }
            $this->children[0][] = $e->id;
        }
    }

    protected function prepareJson($all = true)
    {
        if (empty($this->elements)) {
            $e = [];
            if ($all) {
                $e[] = $this->elementsAdder();
            }
        }
        return $this->addChildren(0, $all);
    }

    protected function addChildren($parent_element_id, $all = true)
    {
        $e = [];
        $i = 0;
        if (!empty($this->children[$parent_element_id])) {
            foreach ($this->children[$parent_element_id] as $id) {
                $e[$i] = $this->elements[$id];
                $e[$i]->children = $this->addChildren($id, $all);
                $i++;
            }
        }
        if ($all) {
//            $e[$i] = $this->elementsAdder($parent_element_id, $i);
        }

        return $e;
    }

    protected function elementForJson($e)
    {
        $o = new \stdClass();
        // $o->icon = 'fa fa-plus-circle text-success';
        $o->text = empty($e->name) ? '' : $e->name . ' ';
        $o->text .= empty($e->enumeration) ? '' : $e->enumeration . ' ';
        $o->text .= empty($e->title) ? '' : $e->title . ' ';
//        $o->text .= mb_substr($e->content, 0, 50) . (mb_strlen($e->content) > 50 ? '...' : '');
//        $o->text = mb_substr($o->text, 0, 100) . (mb_strlen($o->text) > 100 ? '...' : '');

        $o->text .= empty($e->content) ? '' : $e->content . ' ';
        $o->text .= empty($e->text) ? '' : $e->text . ' ';
        if (!empty($this->opened[$e->id])) {
            $o->opened = true;
        } else {
            $o->opened = false;
        }
        $o->options = new \stdClass();
        $o->options->edit = true;
        $o->selected = false;
        $o->loading = false;
        $o->data = new \stdClass();
        $o->data->id = $e->id;
        $o->data->name = $e->name;
        $o->data->enumeration = $e->enumeration;
        $o->data->title = $e->title;
        $o->data->content = $e->content;
        $o->data->start_at = $e->start_at;
        $o->data->end_at = $e->end_at;
        $o->data->position = $e->position;
        $o->data->parent_element_id = $e->parent_element_id;
        $o->data->depth = $e->depth;
        $o->data->out_date = $e->out_date;
        $o->data->after_date = $e->after_date;
        $o->data->before_date = $e->before_date;

        $o->data->audio_url = $e->getFirstMedia();
        if(!empty($o->data->audio_url)){
            $o->data->audio_url = $o->data->audio_url->getUrl();
        }
        return $o;
    }

    protected function elementsAdder($parent_element_id = null, $position = 0)
    {
        $o = new \stdClass();
        $o->icon = 'fa fa-plus-circle text-success';
        $o->text = 'Dodaj element';
        $o->options = new \stdClass();
        $o->options->edit = false;
        $o->options->click = 'elementEdit';
        $o->data = new \stdClass();
        $o->data->id = 0;
        $o->data->name = '';
        $o->data->enumeration = '';
        $o->data->title = '';
        $o->data->content = '';
        $o->data->start_at = null;
        $o->data->end_at = null;
        $o->data->position = $position;
        $o->data->parent_element_id = $parent_element_id;
        if (empty($parent_element_id)) {
            $o->data->depth = 0;
        } else {
            $o->data->depth = $this->elements[$parent_element_id]->data->depth + 1;
        }
        return $o;
    }

    public function elementRemove(Request $request)
    {

        $lde = LawDocumentElement::find($request->input('id'));
        $pei = $lde->parent_element_id;
        $ldi = $lde->law_document_id;
        $p = $lde->position;
        $lde_id = $lde->id;
        ApiLawController::update_question_old($lde_id, true);


        $lde->delete();


        LawDocument::clearCache();
        if (empty($lde)) {
            return response()->json(['message' => 'Element not found with the given ID'], 404);
        }
        if (empty($pei)) {
            LawDocumentElement::where('law_document_id', $ldi)->whereNull('parent_element_id')->where('position', '>=', $p)->update(['position' => DB::raw('position-1')]);
        } else {
            LawDocumentElement::where('law_document_id', $ldi)->where('parent_element_id', $pei)->where('position', '>=', $p)->update(['position' => DB::raw('position-1')]);
        }
        return response()->json();
    }

    public function elementUpdate(Request $request)
    {
        $current_time = \Carbon\Carbon::now();
        $ld = (object)$request->input('document');
        $lden = (object)$request->input('element');
        if (empty($lden->id)) {
            $lde = new LawDocumentElement();
            $lde->law_document_id = $ld->id;
            $lde->parent_element_id = $lden->parent_element_id == 0 ? null : $lden->parent_element_id;
            $lde->name = $lden->name;
            $lde->enumeration = $lden->enumeration;
            $lde->title = $lden->title;
            $lde->content = $lden->content;

            $lde->position = $lden->position;
            $lde->depth = $lden->depth;
            $lde->start_at = $lden->start_at;
            $lde->end_at = $lden->end_at;
            $lde->after_date = ApiQuestionsController::checkLegal_End($lden, $current_time);
            $lde->before_date = ApiQuestionsController::checkLegal_Start($lden, $current_time);
            $lde->out_date = $lde->after_date | $lde->before_date;


            if (empty($lde->parent_element_id)) {
                // $list = LawDocumentElement::where('law_document_id')->whereNull('parent_element_id')->where('position','>=',$lde->position)->get();
                // foreach($list as $e) {
                //     $e->position = $e->position+1;
                //     $e->save();
                // }
                LawDocumentElement::where('law_document_id', $lde->law_document_id)->whereNull('parent_element_id')->where('position', '>=', $lde->position)->update(['position' => DB::raw('position+1')]);
            } else {
                // $list = LawDocumentElement::where('law_document_id')->where('parent_element_id', $lde->parent_element_id)->where('position','>=',$lde->position)->get();
                // foreach($list as $e) {
                //     $e->position = $e->position+1;
                //     $e->save();
                // }
                LawDocumentElement::where('law_document_id', $lde->law_document_id)->where('parent_element_id', $lde->parent_element_id)->where('position', '>=', $lde->position)->update(['position' => DB::raw('position+1')]);
            }
            $lde->save();
            LawDocument::clearCache();
            return response()->json();
        }
        $lde = LawDocumentElement::find($lden->id);
        if (empty($lde)) {
            return response()->json(['message' => 'Element not found with the given ID'], 404);
        }
        $lde->name = $lden->name;
        $lde->enumeration = $lden->enumeration;

        $old_status = false;
        if (($lde->title !== $lden->title) | ($lde->content !== $lden->content)) {
            $old_status = true;
        }

        $lde->title = $lden->title;
        $lde->content = $lden->content;
        $lde->start_at = $lden->start_at;
        $lde->end_at = $lden->end_at;

        $lde->after_date = ApiQuestionsController::checkLegal_End($lden, $current_time);
        $lde->before_date = ApiQuestionsController::checkLegal_Start($lden, $current_time);
        $lde->out_date = $lde->after_date | $lde->before_date;
        $lde->save();


        ApiLawController::update_question_old($lde->id, $old_status);

        LawDocument::clearCache();
        return response()->json();
    }

    public function elementUpdateAudio(Request $request)
    {
        $ele_id = $request->input('element_id');
        $lde = LawDocumentElement::find($ele_id);
        if (empty($lde)) {
            return response()->json(['message' => 'Element not found with the given ID'], 404);
        }

        $m = $lde->getFirstMedia();
        if (!empty($m)) {
            $m->delete();
        }

        $lde->addMedia($request->file('audio')->getPathName())->toMediaCollection();

        return response()->json(['audio' => "ok"]);
    }
}

