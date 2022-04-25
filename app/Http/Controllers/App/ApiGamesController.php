<?php

namespace App\Http\Controllers\App;

use App\ApiHash;
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
use App\UserGame;
use App\UserGameQuestion;
use App\UserGameParticipant;
use App\UserGameParticipantAnswer;
use App\UserGamesRaceSummary;
use App\QuestionsSet;
use App\Http\Controllers\Controller;
use App\UserTest;
use App\UserTestQuestion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use function foo\func;

class ApiGamesController extends Controller
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

    public function update_name(Request $request)
    {
        $games = UserGame::where('name', 'LIKE', 'Wyścig w%')->get();
        foreach($games as $item){
            $item->name = str_replace('Wyścig w', 'Wyścig z', $item->name);
            $item->save();
        }
        return response()->json($games);
    }

    public function ban(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $u = $request->input('user_id');
        $f = $request->input('flag');
        $k = $request->input('hash_key');

        $g = UserGame::where('hash_key', $k)->first();

        if (empty($g)) {
            return response()->json(['message' => 'Nie znaleziono gry.', 'location' => '/app/games'], 404);
        }

        $g = $this->subGame($g);

        if ($g->user_id != $h->user_id) {
            return response()->json(['message' => 'Nie jesteś właścicielem tej gry'], 409);
        }

        $p = UserGameParticipant::where('user_id', $u)->where('user_game_id', $g->id)->first();

        if (empty($p)) {
            return response()->json(['message' => 'Nie ma takiego gracza w tej grze.'], 409);
        }

        if ($f) {
            $p->banned_at = date('Y-m-d H:i:s');
        } else {
            $p->banned_at = null;
        }

        $p->save();
        return response()->json([]);
    }

    public function delete(Request $request)
    {
        return response()->json([]);
    }

    public function examContinue(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $hash_key = $request->input('hash_key');
        $game = UserGame::with('participants')->where('hash_key', $hash_key)->first();

        if (empty($game)) {
            return response()->json(['message' => 'Nie znaleziono gry'], 404);
        }

        if (empty($game->started_at) || !empty($game->finished_at) || ($game->type != 'exam')) {
            return response()->json([
                'game' => $game,
                'room_refresh' => false,
            ]);
        }

        $p = UserGameParticipant::where('user_game_id', $game->id)->where('user_id', $h->user_id)->first();

        // automatycznie konczymy gre
        if (!$this->haveTime($game)) {

            $game->finished_at = $this->finishTime($game);
            $game->save();

            if (!empty($p->started_at) && empty($p->finished_at)) {
                $p->finished_at = $game->finished_at;
                $p->save();
            }

            return response()->json([
                'game' => $game,
                'room_refresh' => true,
            ]);
        }

        $room_refresh = false;

        if (empty($p->started_at)) {
            $p->started_at = date('Y-m-d H:i:s');
            $p->save();
            $room_refresh = true;
        }

        $game->questions = UserGameQuestion::continueGame($game, $p);
        $game->questions_answered_count = UserGameParticipantAnswer::where('user_game_participant_id', $p->id)->count();

        return response()->json([
            'game' => $game,
            'room_refresh' => $room_refresh,
            'owner' => ($game->user_id == $h->user_id),
            'in_game' => !empty($p) && empty($p->left_at),
            'has_left' => !empty($p->left_at),
            'is_banned' => !empty($p->banned_at),
            'is_ready' => !empty($p->ready),
            'is_host' => !empty($p->owner_host_only),
        ]);
    }

    public function regulations(Request $request)
    {
        $h_key = $request->input('hash_key');
        $g = UserGame::with('regulation')->where('hash_key', $h_key)->first();
        return response()->json($g);
    }

    public function examAnswers(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $gn = $request->input('game');
        $f = $request->input('finish');
        $oot = $request->input('out_of_time');

        $extra_time_for_network = 5;

        $g = UserGame::with('questions')->where('hash_key', $gn['hash_key'])->first();

        if (empty($g) || ($g->type != 'exam')) {
            return response()->json(['message' => 'Nie znaleziono gry'], 404);
        }

        $p = UserGameParticipant::where('user_game_id', $g->id)->where('user_id', $h->user_id)->first();

        if (empty($p)) {
            return response()->json(['message' => 'Nie jesteś graczem w tej grze'], 404);
        }

        if (empty($g->started_at)) {
            return response()->json([
                'redirect' => false,
                'message' => 'Gra się jeszcze nie zaczęła',
            ], 409);
        }

        if (!empty($g->finished_at) && (!empty($p->finished_at) || empty($p->started_at))) {
            return response()->json([
                'redirect' => false,
                'message' => 'Gra się już zakończyła',
            ], 409);
        }

        if (empty($p->started_at)) {
            return response()->json([
                'redirect' => false,
                'message' => 'Nie rozpocząłeś rozgrywki',
            ], 409);
        }

        if (empty($g->finished_at) && !$this->haveTime($g)) {
            $g->finished_at = $this->finishTime($g);
            $g->save();
        }

        if (!empty($g->finished_at)) {
            if (!$this->haveTime($g, $extra_time_for_network)) {
                $p->finished_at = $g->finished_at;
                $s1 = strtotime($p->started_at);
                $s2 = strtotime($p->finished_at);
                $p->completion_seconds = $s2 - $s1;
                $p->save();
                return response()->json([
                    'redirect' => false,
                    'message' => 'Upłynął czas rozgrywki',
                ], 409);
            }
        }

        $a = [];
        $qids = [];
        foreach ($gn['questions'] as $q) {
            if (empty($q['answer'])) {
                continue;
            }
            $a[$q['id']] = $q['answer'];
            $qids[] = $q['id'];
        }
        if (empty($a)) {
            if (empty($f)) {
                throw new Exception('Brak danych do aktualizacji');
            }
            if (!empty($g->finished_at)) {
                $p->finished_at = $g->finished_at;
                $s1 = strtotime($p->started_at);
                $s2 = strtotime($p->finished_at);
                $p->completion_seconds = $s2 - $s1;
                $p->save();
                return response()->json([
                    'redirect' => true,
                    'message' => 'Upłynął czas rozgrywki',
                ], 409);
            }

            $p->finished_at = $this->finishTime($g);
            $s1 = strtotime($p->started_at);
            $s2 = strtotime($p->finished_at);
            $p->completion_seconds = $s2 - $s1;
            $p->save();
            return response()->json(['redirect' => true]);
        }

        $can_update = true;
        $questions = UserGameQuestion::fullGame($g, $p);
        $answers = UserGameParticipantAnswer::fullGame($p);

        if (!empty($a) && $can_update) {
            $old_a = [];
            $aids = [];
            foreach ($answers as $q) {
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

        UserQuestionRepeat::batchReady($h->user_id, $qids);
        $ems = [];
        // zakładamy, że nikomu nie chce się hackować i nie walidujemy correct
        DB::beginTransaction();
        $questions_answered_count = 0;
        $questions_answered_correct_count = 0;
        foreach ($g->questions as $q) {
            $ems[] = "LECE QID [$q->question_id]";
            if (empty($a[$q->question_id])) {
                $ems[] = "pusto QID [$q->question_id]";
                if (!empty($answers[$q->question_id]->user_question_answer_id)) {
                    $questions_answered_count++;
                    $ems[] = 'policzyl odp juz zapisana';
                    if (!empty($answers[$q->question_id]->correct)) {
                        $ems[] = 'policzyl odp juz zapisana jako CORRECT';
                        $questions_answered_correct_count++;
                    }
                }
                continue;
            }
            $ems[] = "jest QID [$q->question_id]";
            if (!empty($answers[$q->question_id]->user_question_answer_id) && !$can_update) {
                $ems[] = 'policzyl odp juz zapisana (CANT UPDATE)';
                $questions_answered_count++;
                if (!empty($answers[$q->question_id]->correct)) {
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

            if (!empty($answers[$q->question_id]->user_question_answer_id) && $old_a[$answers[$q->question_id]->user_question_answer_id] == $a[$q->question_id]['question_option_id']) {
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
            if (empty($answers[$q->question_id])) {
                $answers[$q->question_id] = new UserGameParticipantAnswer();
                $answers[$q->question_id]->question_id = $q->question_id;
                $answers[$q->question_id]->user_game_participant_id = $p->id;
            }

            $answers[$q->question_id]->user_question_answer_id = $uqa->id;
            $answers[$q->question_id]->correct = $a[$q->question_id]['correct'];
            $answers[$q->question_id]->save();
            $ems[] = "ZAKTUALIZOWAL ODP W TESCIE [$uqa->id]";
            UserQuestionRepeat::batchOne($q->question_id, $a[$q->question_id]['correct']);
            $ems[] = "STRZELIL REPEATA [$q->question_id]";
        }
        UserQuestionRepeat::batchSave();
        $p->touch();
        $p->questions_answered_count = $questions_answered_count;
        $p->questions_answered_correct_count = $questions_answered_correct_count;

        $ems[] = "NOOOO???";

        if (!empty($g->finished_at)) {
            $p->finished_at = $g->finished_at;
            $s1 = strtotime($p->started_at);
            $s2 = strtotime($g->finished_at);
            $p->completion_seconds = $s2 - $s1;
            $p->save();
            DB::commit();
            return response()->json([
                'redirect' => true,
                'message' => 'Upłynął czas rozgrywki',
            ], 409);
        }

        if (!$this->haveTime($g)) {
            $p->finished_at = $this->finishTime($g);
            $s1 = strtotime($p->created_at);
            $s2 = strtotime($p->finished_at);
            $p->completion_seconds = $s2 - $s1;
            $p->save();
            DB::commit();
            return response()->json([
                'redirect' => true,
                'message' => 'Upłynął czas rozgrywki',
            ], 409);
        }

        if (!empty($f)) {
            if (!$this->haveTime($g)) {
                $p->finished_at = $this->finishTime($g);
            } else {
                $p->finished_at = date('Y-m-d H:i:s');
            }
            $s1 = strtotime($p->started_at);
            $s2 = strtotime($p->finished_at);
            $p->completion_seconds = $s2 - $s1;
            $p->save();
            $ems[] = "zapis koniec";
            DB::commit();
            return response()->json(['ems' => $ems, 'redirect' => true]);
        }

        $p->save();
        // DB::rollback();
        DB::commit();
        $ems[] = "zapis";
        return response()->json(['ems' => $ems]);
    }

    public function finish(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $k = $request->input('hash_key');
        $g = UserGame::where('hash_key', $k)->first();

        if (empty($g)) {
            return response()->json(['message' => 'Nie znaleziono gry.'], 404);
        }

        if (empty($g->started_at)) {
            return response()->json(['message' => 'Ta gra nie została jeszcze rozpoczęta.'], 409);
        }

        $p = UserGameParticipant::where('user_game_id', $g->id)->where('user_id', $h->user_id)->first();

        $room_refresh = false;

        if (empty($g->finished_at)) {
            if (!$this->haveTime($g)) {
                $g->finished_at = $this->finishTime($g);
                $g->save();
                $room_refresh = true;
            }
        }

        if (empty($p->started_at) || !empty($p->finished_at)) {
            return response()->json([
                'room_refresh' => $room_refresh
            ]);
        }

        $p->finished_at = $this->finishTime($g);
        $s1 = strtotime($p->started_at);
        $s2 = strtotime($p->finished_at);
        $p->completion_seconds = $s2 - $s1;
        $p->save();

        return response()->json([
            'room_refresh' => true
        ]);
    }

    public function finishCheckAll(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $k = $request->input('hash_key');
        $notimecheck = $request->input('no_time_check');
        $notimecheck = empty($notimecheck) ? false : true;
        DB::beginTransaction();
        $g = UserGame::where('hash_key', $k)->lockForUpdate()->first();

        if (empty($g)) {
            DB::rollback();
            return response()->json(['message' => 'Nie znaleziono gry.'], 404);
        }

        if (!empty($g->finish_checked_at)) {
            DB::rollback();
            $g = UserGame::with('participants')->where('hash_key', $k)->first();
            return response()->json([]);
        }

        if (empty($g->started_at)) {
            DB::rollback();
            return response()->json(['message' => 'Ta gra nie została jeszcze rozpoczęta.'], 409);
        }

        if (!$notimecheck && $this->haveTime($g)) {
            DB::rollback();
            return response()->json(['message' => 'Jeszcze jest czas, gra skończy się automatycznie, po upływie czasu.'], 409);
        }

        if (empty($g->finished_at)) {
            $g->finished_at = $this->finishTime($g);
        }

        $g->finish_checked_at = date('Y-m-d H:i:s');
        $ps = UserGameParticipant::where('user_game_id', $g->id)->get();
        foreach ($ps as $p) {
            if (empty($p->started_at) || !empty($p->finished_at)) {
                continue;
            }
            $p->finished_at = $g->finished_at;
            $s1 = strtotime($p->started_at);
            $s2 = strtotime($p->finished_at);
            $p->completion_seconds = $s2 - $s1;
            $p->save();
        }
        $g->save();
        DB::commit();
        $g = UserGame::with('participants')->where('hash_key', $k)->first();
        return response()->json(['game' => $g]);
    }

    protected function finishTime(UserGame $game)
    {
        if (!empty($game->finished_at)) {
            return $game->finished_at;
        }
        if ($this->haveTime($game)) {
            return date('Y-m-d H:i:s');
        }
        $d1 = strtotime($game->started_at);
        $d3 = $game->time_limit * 60;
        return date('Y-m-d H:i:s', $d1 + $d3);
    }

    public function forceFinish(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $k = $request->input('hash_key');

        $g = UserGame::with('participants')->where('hash_key', $k)->where('user_id', $h->user_id)->first();

        if (empty($g)) {
            return response()->json(['message' => 'Nie znaleziono gry.'], 404);
        }

        $g = $this->subGame($g);

        if (!empty($g->finished_at) || !empty($g->removed_at)) {
            return response()->json(['message' => 'Ta gra nie jest już aktywna.'], 404);
        }

        if (!empty($g->started_at)) {
            return response()->json(['message' => 'Ta gra została już rozpoczęta.'], 404);
        }

        $g->finished_at = date('Y-m-d H:i:s');
        $g->finish_checked_at = date('Y-m-d H:i:s');
        $g->finalized_at = date('Y-m-d H:i:s');
        $g->save();

        return response()->json(['game' => $g]);

    }

    protected function haveTime(UserGame $game, $extra = 0)
    {
        $d1 = strtotime($game->started_at);
        $d2 = time();
        $d3 = $game->time_limit * 60;
        return ($d2 - $d1 < $d3 + $extra);
    }

    public function hostOnly(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $k = $request->input('hash_key');

        $g = UserGame::where('hash_key', $k)->first();

        if (empty($g)) {
            return response()->json(['message' => 'Nie znaleziono gry.', 'location' => '/app/games'], 404);
        }

        $g = $this->subGame($g);

        if (!empty($g->finished_at) || !empty($g->removed_at)) {
            return response()->json(['message' => 'Ta gra nie jest już aktywna.'], 404);
        }

        if ($g->user_id != $h->user_id) {
            return response()->json(['message' => 'Nie możesz zostać hostem nie swojej gry.'], 409);
        }

        $p = UserGameParticipant::where('user_id', $h->user_id)->where('user_game_id', $g->id)->first();

        $p->owner_host_only = true;
        $p->ready = false;
        $p->save();

        return response()->json();
    }

    public function join(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $k = $request->input('hash_key');

        $g = UserGame::where('hash_key', $k)->first();

        if (empty($g)) {
            return response()->json(['message' => 'Nie znaleziono gry.', 'location' => '/app/games'], 404);
        }


        if ($g->user_id == $h->user_id) {
            if ($g->type == 'exam') {
                return response()->json(['message' => 'To Twoja gra, nie mogę Cię ponownie dołączyć. Użyj linku by zaprosić znajomych.', 'location' => '/app/games/view/exam/' . $g->hash_key], 409);
            }
            if ($g->type == 'race') {
                return response()->json(['message' => 'To Twoja gra, nie mogę Cię ponownie dołączyć. Użyj linku by zaprosić znajomych.', 'location' => '/app/games/view/race/' . $g->hash_key], 409);
            }
            return response()->json(['message' => '', 'location' => '/app/games'], 409);
        }

        $p = UserGameParticipant::where('user_id', $h->user_id)->where('user_game_id', $g->id)->first();

        if (!empty($p)) {
            if ($g->type == 'exam') {
                return response()->json(['message' => 'Już jesteś graczem w tej grze.', 'location' => '/app/games/view/exam/' . $g->hash_key], 409);
            }
            if ($g->type == 'race') {
                return response()->json(['message' => 'Już jesteś graczem w tej grze.', 'location' => '/app/games/view/race/' . $g->hash_key], 409);
            }
        }

        if (!empty($g->finished_at) || !empty($g->removed_at)) {
            return response()->json(['message' => 'Ta gra nie jest już aktywna.', 'location' => '/app/games'], 404);
        }


        $u = User::find($h->user_id);
        $p = UserGameParticipant::addToGame($g->id, $u);
        if (empty($p)) {
            return response()->json(['message' => 'Wystąpił błąd.', 'location' => '/app/games'], 409);
        }

        return response()->json(['game' => $g]);
    }

    public function leave(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $k = $request->input('hash_key');

        $g = UserGame::where('hash_key', $k)->first();

        if (empty($g)) {
            return response()->json(['message' => 'Nie znaleziono gry.', 'location' => '/app/games'], 404);
        }

        if (!empty($g->finished_at) || !empty($g->removed_at)) {
            return response()->json(['message' => 'Ta gra nie jest już aktywna.', 'location' => '/app/games'], 404);
        }

        $p = UserGameParticipant::where('user_id', $h->user_id)->where('user_game_id', $g->id)->first();

        if (empty($p)) {
            return response()->json(['message' => 'Nie jesteś graczem w tej grze.'], 409);
        }

        if (!empty($p->left_at)) {
            return response()->json([]);
        }

        $p->left_at = date('Y-m-d H:i:s');
        $p->save();

        return response()->json([]);
    }

    public function finished(Request $request)
    {
        // return game list finished with parameter
        $h = ApiHash::find($request->input('hash_id'));
        $participants = UserGameParticipant::with('user_game')->where('user_id', $h->user_id)->where('questions_answered_count', '>', 0)
            ->whereHas('user_game', function ($query) {
                $query->whereNotNull('finished_at')->whereNull('root_game_id');
            })->orderBy('updated_at', 'desc')->get();

        foreach($participants as $item){
            $sub_games = UserGameParticipant::with('user_game')->where('user_id', $h->user_id)->where('questions_answered_count', '>', 0)
                ->whereHas('user_game', function ($query) use($item) {
                    $query->where('root_game_id', $item->id);
                })->get();
            $item->sub_games = $sub_games;
        }
        return response()->json([
            'list' => $participants,
        ]);

    }

    public function list(Request $request)
    {

        $h = ApiHash::find($request->input('hash_id'));
        $f = $request->input('filter');
        $ids = UserGameParticipant::gameIds($h->user_id);
        $q = UserGame::withCount('participants')->whereNull('root_game_id')->whereIn('id', $ids)->orderBy('updated_at', 'desc')->get();

        $q_active = [];
        $q_inactive = [];
        foreach ($q as $q_item) {
            $last_game = $this->lastGame($q_item);
            $last_game = empty($last_game) ? $q_item : $last_game;

            if (!empty($last_game)) {
                if ($last_game->finished_at) {
                    $q_inactive[] = $q_item;
                } else {
                    $q_active[] = $q_item;
                }
            }

        }

        if ($f == 'finished') {
            $result = $q_inactive;
        } else {
            $result = $q_active;
        }

        return response()->json([
            'list' => $result,
        ]);

    }


    public function new(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $hk = $request->input('hash_key');

        $root_game = null;
        $last_game = null;
        if (!empty($hk)) {
            $root_game = UserGame::where('hash_key', $hk)->where('user_id', $h->user_id)->whereNull('finalized_at')->first();
            if (empty($root_game)) {
                $root_game = null;
            } else {
                $last_game = $this->lastGame($root_game);
                $next_game = $this->nextGame($root_game);
                if (!empty($next_game)) {
                    return response()->json(['message' => 'Istnieje już aktywna gra'], 409);
                }
            }
        }

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

        if (empty($ids_ok)  && empty($admin_tests_questions)) {
            return response()->json(['message' => 'Brak wybranego zestawu'], 404);
        }
        $info = true;
        if (count($ids_ok) == 1) {
            $name = ($settings['type'] == 'exam' ? 'Egzamin z ' : 'Wyścig z ') . $ids_ok_names[0];
            $info = '';
        } elseif (count($groups_ok) && !in_array(0, $groups_partial) && $groups_ok === $groups_partial) {
            if (count($groups_ok) > 1) {
                $name = $settings['type'] == 'exam' ? 'Egzamin Mieszany' : 'Wyścig Mieszany';
            } else {
                $name = $groups_ok_names[0];
                $info = '';
            }
        } elseif (!in_array(0, $groups_partial) && count($groups_partial) == 1) {
            $name = ($settings['type'] == 'exam' ? 'Egzamin z ' : 'Wyścig z ') . $groups_partial_names[0];
        } else {
            $name = $settings['type'] == 'exam' ? 'Egzamin Mieszany' : 'Wyścig Mieszany';
        }

        if (!empty($settings['custom_name']) && !empty($settings['name'])) {
            $name = $settings['name'];
        }

        $starts_at = null;

        if (!empty($settings['custom_time'])) {
            if (empty($settings['starts_at'])) {
                return response()->json(['message' => 'Ustaw czas startu lub wyłącz opcję'], 409);
            }
            if (!empty($settings['starts_at']) && strtotime($settings['starts_at']) < time()) {
                return response()->json(['message' => 'Nie można ustawić czasu startu w przeszłości'], 409);
            }
            $starts_at = empty($settings['starts_at']) ? null : date('Y-m-d H:i', strtotime($settings['starts_at']));
        }

        if (!empty($info)) {
            $info = join(', ', $ids_ok_names);
        }

        $res = ApiTestsController::add_selected_law($elements, [], $settings['set_date']);

        if (!empty($settings['only_new'])) {
            $questions_ids = UserQuestionNew::available_ids($h->user_id, $ids_ok, $settings['limit'], $res, $settings['set_date']);
            $admin_tests_questions = ApiTestsController::get_new_questions($admin_tests_questions, $h->user_id);

        } elseif (!empty($settings['only_known'])) {
            $questions_ids = UserQuestionKnown::available_ids($h->user_id, $ids_ok, $settings['limit'], $res, $settings['set_date']);
            $admin_tests_questions = ApiTestsController::get_known_questions($admin_tests_questions, $h->user_id);
        } else {
            $questions_ids = UserQuestion::available_ids($h->user_id, $ids_ok, $settings['limit'], $res, $settings['set_date']);
        }

        $questions_ids = array_merge($questions_ids, $admin_tests_questions);

        $root_game_id = empty($root_game) ? null : $root_game->id;

        $ug = DB::transaction(function () use ($questions_ids, $name, $info, $settings, $h, $starts_at, $root_game_id) {
            $ug = new UserGame();
            $ug->hash_key = uniqid();
            $ug->user_id = $h->user_id;
            $ug->name = $name;
            $ug->info = $info;
            $ug->type = $settings['type'];
            $ug->time_limit = $settings['time'];
            $ug->questions_count = count($questions_ids);
            $ug->starts_at = $starts_at;
            $ug->auto_start = !empty($starts_at) && !empty($settings['auto_start']);
            $ug->root_game_id = $root_game_id;
            $ug->regula = $settings['regula'];
            $ug->regula_id = $settings['regula_id'];
            $ug->save();
            foreach ($questions_ids as $qid) {
                $ugq = new UserGameQuestion();
                $ugq->user_game_id = $ug->id;
                $ugq->question_id = $qid;
                $ugq->save();
            }
            return $ug;
        });

        if (empty($root_game)) {
            $user = User::find($h->user_id);
            UserGameParticipant::addToGame($ug->id, $user, true);

            return response()->json(['game' => $ug, 'root_game' => $root_game]);
        }

        $gid = empty($last_game) ? $root_game->id : $last_game->id;

        $plast = UserGameParticipant::where('user_game_id', $gid)->whereNull('left_at')->whereNull('banned_at')->get();

        foreach ($plast as $pl) {
            $p = new UserGameParticipant();
            $p->user_game_id = $ug->id;
            $p->user_id = $pl->user_id;
            $p->owner = $pl->owner;
            $p->owner_host_only = $pl->owner_host_only;
            $p->name = $pl->name;
            $p->save();
        }

        return response()->json(['game' => $ug, 'root_game' => $root_game]);
    }

//    public function adminGameNew(Request $request)
//    {
//        $h = ApiHash::find($request->input('hash_id'));
//        $hk = $request->input('hash_key');
//        $package_id = $request->input('package_id');
//        $package_name = $request->input('package_name');
//
//
//        $root_game = null;
//        $last_game = null;
//
//        if (!empty($hk)) {
//            $root_game = UserGame::where('hash_key',$hk)->where('user_id',$h->user_id)->whereNull('finalized_at')->first();
//            if (empty($root_game)) {
//                $root_game = null;
//            } else {
//                $last_game = $this->lastGame($root_game);
//                $next_game = $this->nextGame($root_game);
//                if (!empty($next_game)) {
//                    return response()->json(['message'=>'Istnieje już aktywna gra'], 409);
//                }
//            }
//        }
//
//        $ids_ok_names = [];
//        $settings = $request->input('settings');
//        $info=true;
//
//        $name = ($settings['type'] == 'exam' ? 'Egzamin ' : 'Wyścig ').$package_name;
//
//        if (!empty($settings['custom_name']) && !empty($settings['name'])) {
//            $name = $settings['name'];
//        }
//
//        $starts_at = null;
//
//        if (!empty($settings['custom_time'])) {
//            if (empty($settings['starts_at'])) {
//                return response()->json(['message' => 'Ustaw czas startu lub wyłącz opcję'],409);
//            }
//            if (!empty($settings['starts_at']) && strtotime($settings['starts_at']) < time()) {
//                return response()->json(['message' => 'Nie można ustawić czasu startu w przeszłości'],409);
//            }
//            $starts_at = empty($settings['starts_at']) ? null : date('Y-m-d H:i',strtotime($settings['starts_at']));
//        }
//
//        if (!empty($info)) {
//            $info = join(', ',$ids_ok_names);
//        }
//
//        $questions_ids = $request->input('questions');
//
//        $root_game_id = empty($root_game) ? null : $root_game->id;
//
//        $ug = DB::transaction(function () use($questions_ids, $name, $info, $settings, $h, $starts_at,$root_game_id) {
//            $ug = new UserGame();
//            $ug->hash_key = uniqid();
//            $ug->user_id = $h->user_id;
//            $ug->name = $name;
//            $ug->info = $info;
//            $ug->type = $settings['type'];
//            $ug->time_limit = $settings['time'];
//            $ug->questions_count = count($questions_ids);
//            $ug->starts_at = $starts_at;
//            $ug->auto_start = !empty($starts_at) && !empty($settings['auto_start']);
//            $ug->root_game_id = $root_game_id;
//            $ug->regula = $settings['regula'];
//            $ug->regula_id = $settings['regula_id'];
//            $ug->save();
//            foreach($questions_ids as $qid) {
//                $ugq = new UserGameQuestion();
//                $ugq->user_game_id = $ug->id;
//                $ugq->question_id = $qid;
//                $ugq->save();
//            }
//            return $ug;
//        });
//
//        if (empty($root_game)) {
//            $user = User::find($h->user_id);
//            UserGameParticipant::addToGame($ug->id,$user,true);
//
//            //users purchased packages
//            $user_packages = UserPackage::where('package_id', '=', $package_id)->where('valid_until', '>=', date('Y-m-d H:i:s'))->get();
//            $users = [];
//
//            foreach($user_packages as $item1){
//                array_push($users, $item1);
//                if($item1->user_id !== $h->user_id){
//                    UserGameParticipant::addToGame($ug->id, $item1->user);
//                }
//
//            }
//
//            return response()->json(['game'=>$ug,'root_game'=>$root_game, 'user'=>$users]);
//        }
//
//        $gid = empty($last_game) ? $root_game->id : $last_game->id;
//
//        $plast = UserGameParticipant::where('user_game_id',$gid)->whereNull('left_at')->whereNull('banned_at')->get();
//
//        foreach($plast as $pl) {
//            $p = new UserGameParticipant();
//            $p->user_game_id = $ug->id;
//            $p->user_id = $pl->user_id;
//            $p->owner = $pl->owner;
//            $p->owner_host_only = $pl->owner_host_only;
//            $p->name = $pl->name;
//            $p->save();
//        }
//
//        return response()->json(['game'=>$ug,'root_game'=>$root_game,]);
//    }

    public function raceContinue(Request $request)
    {

        $h = ApiHash::find($request->input('hash_id'));
        $hash_key = $request->input('hash_key');
        $game = UserGame::where('hash_key', $hash_key)->first();

        if (empty($game)) {
            return response()->json(['message' => 'Nie znaleziono gry'], 404);
        }

        $game = $this->subGame($game);

        if (empty($game->started_at) || !empty($game->finished_at) || ($game->type != 'race')) {
            return response()->json([
                'game' => $game,
                'room_refresh' => false,
            ]);
        }

        $p = UserGameParticipant::where('user_game_id', $game->id)->where('user_id', $h->user_id)->first();

        $room_refresh = false;

        if (empty($p->started_at)) {
            $p->started_at = date('Y-m-d H:i:s');
            $p->save();
            $room_refresh = true;
        }

        $game->questions = UserGameQuestion::continueGame($game, $p);
        $game->questions_answered_count = UserGameParticipantAnswer::where('user_game_participant_id', $p->id)->count();

        return response()->json([
            'game' => $game,
            'room_refresh' => $room_refresh,
            'owner' => ($game->user_id == $h->user_id),
            'in_game' => !empty($p) && empty($p->left_at),
            'has_left' => !empty($p->left_at),
            'is_banned' => !empty($p->banned_at),
            'is_ready' => !empty($p->ready),
            'is_host' => !empty($p->owner_host_only),
        ]);

    }

    public function raceAnswers(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $gn = $request->input('game');
        $f = $request->input('finish');
        $oot = $request->input('out_of_time');

        $g = UserGame::with('questions')->where('hash_key', $gn['hash_key'])->first();

        if (empty($g) || ($g->type != 'race')) {
            return response()->json(['message' => 'Nie znaleziono gry'], 404);
        }

        $g = $this->subGame($g);

        $p = UserGameParticipant::where('user_game_id', $g->id)->where('user_id', $h->user_id)->first();

        if (empty($p)) {
            return response()->json(['message' => 'Nie jesteś graczem w tej grze'], 404);
        }

        if (empty($g->started_at)) {
            return response()->json([
                'redirect' => false,
                'message' => 'Gra się jeszcze nie zaczęła',
            ], 409);
        }

        if (!empty($g->finished_at) && (!empty($p->finished_at) || empty($p->started_at))) {
            return response()->json([
                'redirect' => true,
                'message' => 'Gra się już zakończyła',
            ], 409);
        }

        if (empty($p->started_at)) {
            return response()->json([
                'redirect' => true,
                'message' => 'Nie rozpocząłeś rozgrywki',
            ], 409);
        }

        if (!empty($g->finished_at)) {
            $p->finished_at = $g->finished_at;
            $s1 = strtotime($p->started_at);
            $s2 = strtotime($p->finished_at);
            $p->completion_seconds = $s2 - $s1;
            $p->save();
            return response()->json([
                'redirect' => true,
                'message' => 'Gra zakończyła się, zwyciężył inny gracz',
            ], 409);
        }

        $a = [];
        $qids = [];
        foreach ($gn['questions'] as $q) {
            if (empty($q['answer'])) {
                continue;
            }
            $a[$q['id']] = $q['answer'];
            $qids[] = $q['id'];
        }
        if (empty($a)) {
            if (empty($f)) {
                throw new \Exception('Brak danych do aktualizacji');
            }
            if ($g->questions_count != $p->questions_answered_correct_count) {
                return response()->json([
                    'redirect' => true,
                    'message' => 'Problem z zapisem końca gry, nastąpi przekierowanie/odświeżenie',
                ], 409);
            }
            $g->finished_at = date('Y-m-d H:i:s');
            $g->save();
            $p->finished_at = $g->finished_at;
            $s1 = strtotime($p->started_at);
            $s2 = strtotime($p->finished_at);
            $p->completion_seconds = $s2 - $s1;
            $p->save();
            return response()->json([
                'redirect' => true,
                'finished' => true,
            ]);
        }

        $can_update = false;
        $questions = UserGameQuestion::fullGame($g, $p);
        $answers = UserGameParticipantAnswer::fullGame($p);

        // if (!empty($a) && $can_update) {
        //     $old_a=[];
        //     $aids=[];
        //     foreach($answers as $q) {
        //         if (!empty($q->user_question_answer_id)) {
        //           $aids[] = $q->user_question_answer_id;
        //         }
        //     }
        //     $ems[]='aids';
        //     $ems[]=$aids;
        //     if (!empty($aids)) {
        //         $tmp_a = UserQuestionAnswer::whereIn('id',$aids)->get();
        //         foreach($tmp_a as $uqa) {
        //             $old_a[$uqa->id]= $uqa->question_option_id;
        //         }
        //     }
        //     $ems[]='old_a';
        //     $ems[]=$old_a;
        // }

        UserQuestionRepeat::batchReady($h->user_id, $qids);
        $ems = [];
        // zakładamy, że nikomu nie chce się hackować i nie walidujemy correct
        DB::beginTransaction();
        $questions_answered_count = 0;
        $questions_answered_correct_count = 0;
        foreach ($g->questions as $q) {
            $ems[] = "LECE QID [$q->question_id]";
            if (empty($a[$q->question_id])) {
                $ems[] = "pusto QID [$q->question_id]";
                if (!empty($answers[$q->question_id]->user_question_answer_id)) {
                    $questions_answered_count++;
                    $ems[] = 'policzyl odp juz zapisana';
                    if (!empty($answers[$q->question_id]->correct)) {
                        $ems[] = 'policzyl odp juz zapisana jako CORRECT';
                        $questions_answered_correct_count++;
                    }
                }
                continue;
            }
            $ems[] = "jest QID [$q->question_id]";
            if (!empty($answers[$q->question_id]->user_question_answer_id) && !$can_update) {
                $ems[] = 'policzyl odp juz zapisana (CANT UPDATE)';
                $questions_answered_count++;
                if (!empty($answers[$q->question_id]->correct)) {
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

            if (!empty($answers[$q->question_id]->user_question_answer_id) && $old_a[$answers[$q->question_id]->user_question_answer_id] == $a[$q->question_id]['question_option_id']) {
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
            if (empty($answers[$q->question_id])) {
                $answers[$q->question_id] = new UserGameParticipantAnswer();
                $answers[$q->question_id]->question_id = $q->question_id;
                $answers[$q->question_id]->user_game_participant_id = $p->id;
            }

            $answers[$q->question_id]->user_question_answer_id = $uqa->id;
            $answers[$q->question_id]->correct = $a[$q->question_id]['correct'];
            $answers[$q->question_id]->save();
            $ems[] = "ZAKTUALIZOWAL ODP W TESCIE [$uqa->id]";
            Log::debug('answer:' . $q->question_id . "  &a:" . $a[$q->question_id]['correct']);
            UserQuestionRepeat::batchOne($q->question_id, $a[$q->question_id]['correct']);
            $ems[] = "STRZELIL REPEATA [$q->question_id]";
        }
        UserQuestionRepeat::batchSave();
        $p->touch();
        $p->questions_answered_count = $questions_answered_count;
        $p->questions_answered_correct_count = $questions_answered_correct_count;

        $ems[] = "NOOOO???";

        if ($p->questions_answered_correct_count == $g->questions_count) {
            $g->finished_at = date('Y-m-d H:i:s');
            $g->save();
            $p->finished_at = $g->finished_at;
            $s1 = strtotime($p->started_at);
            $s2 = strtotime($p->finished_at);
            $p->completion_seconds = $s2 - $s1;
            $p->save();
            $ems[] = "zapis koniec";
            DB::commit();

            $this->raceFinishAllCheck($g);

            return response()->json(['ems' => $ems, 'redirect' => true, 'finished' => true]);
        }

        $p->save();
        // DB::rollback();
        DB::commit();

        if (!empty($f)) {
            return response()->json([
                'redirect' => true,
                'message' => 'Problem z zapisem końca gry, nastąpi przekierowanie/odświeżenie',
            ], 409);
        }

        $ems[] = "zapis";
        return response()->json(['ems' => $ems]);
    }

    public function raceFinishAllCheck($g)
    {
        $ps = UserGameParticipant::where('user_game_id', $g->id)->whereNotNull('started_at')->whereNull('finished_at')->get();

        foreach ($ps as $p) {
            $p->finished_at = $g->finished_at;
            $p->save();
        }

        $g->finish_checked_at = date('Y-m-d H:i:s');
        $g->save();
    }

    public function raceParticipants(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $hash_key = $request->input('hash_key');
        $game = UserGame::where('hash_key', $hash_key)->first();

        if (empty($game)) {
            return response()->json(['message' => 'Nie znaleziono gry'], 404);
        }

        $game = $this->subGame($game);

        if (empty($game->started_at) || !empty($game->finished_at)) {
            return response()->json(['message' => 'Gra zakończona'], 409);
        }

        $p = UserGameParticipant::where('user_game_id', $game->id)->whereNotNull('started_at')->orderBy('questions_answered_correct_count', 'desc')->get();

        return response()->json([
            'participants' => $p,
        ]);
    }

    public function ready(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $k = $request->input('hash_key');

        $g = UserGame::where('hash_key', $k)->first();

        if (empty($g)) {
            return response()->json(['message' => 'Nie znaleziono gry.', 'location' => '/app/games'], 404);
        }

        $g = $this->subGame($g);

        if (!empty($g->finished_at) || !empty($g->removed_at)) {
            return response()->json(['message' => 'Ta gra nie jest już aktywna.', 'location' => '/app/games'], 404);
        }

        $p = UserGameParticipant::where('user_id', $h->user_id)->where('user_game_id', $g->id)->first();

        if (empty($p)) {
            return response()->json(['message' => 'Nie jesteś graczem w tej grze.'], 409);
        }

        if (!empty($p->left_at)) {
            return response()->json(['message' => 'Opuściłeś grę, przyłącz się najpierw ponownie.'], 409);
        }

        if (!empty($p->banned_at)) {
            return response()->json(['message' => 'Jesteś zablokowany przez administratora gry.'], 409);
        }

        $p->owner_host_only = false;
        $p->ready = true;
        $p->save();

        return response()->json([]);
    }

    public function start(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $k = $request->input('hash_key');

        $g = UserGame::where('hash_key', $k)->first();

        if (empty($g)) {
            return response()->json(['message' => 'Nie znaleziono gry.', 'location' => '/app/games'], 404);
        }

        $g = $this->subGame($g);

        if (!empty($g->finished_at) || !empty($g->removed_at)) {
            return response()->json(['message' => 'Ta gra jest już zakończona lub usunięta.'], 409);
        }

        if (empty($g->auto_start) && $g->user_id != $h->user_id) {
            return response()->json(['message' => 'Tylko właściciel może wymusić start gry.'], 409);
        }

        if (!empty($g->started_at)) {
            return response()->json(['started_at' => $g->started_at]);
        }

        $p = UserGameParticipant::where('user_id', $h->user_id)->where('user_game_id', $g->id)->first();

        if (empty($p)) {
            return response()->json(['message' => 'Nie jesteś graczem w tej grze.'], 409);
        }

        $g->started_at = date('Y-m-d H:i:s');
        if($g->auto_start){
            if($g->starts_at > $g->started_at){
                return response()->json(['message' => 'Tylko właściciel może wymusić start gry.'], 409);
            }
            $g->started_at = $g->starts_at;
        }
        $g->save();

        return response()->json(['started_at' => $g->started_at]);
    }

    public function unflag(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $k = $request->input('hash_key');

        $g = UserGame::where('hash_key', $k)->first();

        if (empty($g)) {
            return response()->json(['message' => 'Nie znaleziono gry.', 'location' => '/app/games'], 404);
        }

        $g = $this->subGame($g);

        if (!empty($g->finished_at) || !empty($g->removed_at)) {
            return response()->json(['message' => 'Ta gra nie jest już aktywna.', 'location' => '/app/games'], 404);
        }

        $p = UserGameParticipant::where('user_id', $h->user_id)->where('user_game_id', $g->id)->first();

        if (empty($p)) {
            return response()->json(['message' => 'Nie jesteś graczem w tej grze.'], 409);
        }

        $p->owner_host_only = false;
        $p->ready = false;
        $p->save();

        return response()->json([]);
    }

    public function viewExam(Request $request)
    {

        $h = ApiHash::find($request->input('hash_id'));
        $hash_key = $request->input('hash_key');
        $game = UserGame::with(['participants' => function ($q) {
            $q->orderBy('questions_answered_correct_count', 'desc');
        }, 'regulation'])->where('hash_key', $hash_key)->first();

        if (empty($game)) {
            return response()->json(['message' => 'Nie znaleziono gry'], 404);
        }

        $p = UserGameParticipant::where('user_game_id', $game->id)->where('user_id', $h->user_id)->first();
        $can_finish = UserGameParticipant::where('user_game_id', $game->id)->whereNull('banned_at')->whereNull('left_at')->where('owner_host_only', 0)->whereNull('finished_at')->count() == 0;

        return response()->json([
            'game' => $game,
            'owner' => ($game->user_id == $h->user_id),
            'in_game' => !empty($p) && empty($p->left_at),
            'has_left' => !empty($p->left_at),
            'has_finished' => !empty($p->finished_at),
            'is_banned' => !empty($p->banned_at),
            'is_ready' => !empty($p->ready),
            'is_host' => !empty($p->owner_host_only),
            'can_finish' => $can_finish,
        ]);
    }

    public function viewRace(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $hash_key = $request->input('hash_key');
        $game = UserGame::with(['participants' => function ($q) {
            $q->orderBy('questions_answered_correct_count', 'desc');
        }])->where('hash_key', $hash_key)->first();

        if (empty($game)) {
            return response()->json(['message' => 'Nie znaleziono gry'], 404);
        }

        if ($game->type != 'race') {
            return response()->json(['message' => 'Nie znaleziono gry.', 'location' => '/app/games'], 404);
        }

        $next_game = $this->nextGame($game);
        $last_game = $this->lastGame($game);
        $all_games = $this->allGames($game);

        $last_game = empty($last_game) ? $game : $last_game;
        $next_game = empty($next_game) ? $last_game : $next_game;

        $p = UserGameParticipant::where('user_game_id', $next_game->id)->where('user_id', $h->user_id)->first();



        return response()->json([
            'root_game' => $game,
            'next_game' => $next_game,
            'all_games' => $all_games,
            'summary' => UserGamesRaceSummary::where('user_game_id', $game->id)->orderBy('questions_answered_correct_count', 'desc')->get(),
            'owner' => ($game->user_id == $h->user_id),
            'in_game' => !empty($p) && empty($p->left_at),
            'has_left' => !empty($p->left_at),
            'has_finished' => !empty($p->finished_at),
            'is_banned' => !empty($p->banned_at),
            'is_ready' => !empty($p->ready),
            'is_host' => !empty($p->owner_host_only),
            'game_can_add' => $this->canAdd($game),
            'server_time' => Carbon::now()->toDateTimeString(),
        ]);
    }

    public function canAdd(UserGame $game)
    {
        if (empty($game->finished_at) || !empty($game->finalized_at)) {
            return false;
        }

        return UserGame::where('root_game_id', $game->id)->whereNull('finished_at')->count() == 0;
    }

    public function nextGame(UserGame $game)
    {
        return UserGame::where('root_game_id', $game->id)->with(['participants' => function ($q) {
            $q->orderBy('questions_answered_correct_count', 'desc');
        }])->whereNull('finished_at')->first();
    }

    public function lastGame(UserGame $game)
    {
        return UserGame::where('root_game_id', $game->id)->with(['participants' => function ($q) {
            $q->orderBy('questions_answered_correct_count', 'desc');
        }])->orderBy('id', 'desc')->first();
    }

    public function allGames(UserGame $game)
    {
        return UserGame::where('root_game_id', $game->id)->orWhere('id', $game->id)->with(['participants' => function ($q) {
            $q->orderBy('questions_answered_correct_count', 'desc');
        }])->orderBy('id', 'desc')->get();
    }

    public function subGame(UserGame $game)
    {
        if ($game->type == 'exam') {
            return $game;
        }
        $next = $this->nextGame($game);
        if (!empty($next)) {
            return $next;
        }
        return $game;
    }

    public function summary(Request $request)
    {
        $h = ApiHash::find($request->input('hash_id'));
        $f = $request->input('filter');
        $id = $request->input('id');
        $test = UserGameParticipant::with('user_game')->where('user_id', $h->user_id)->where('id', $id)->first();
        if (empty($test) || empty($test->finished_at)) {
            return response()->json(['message' => 'Nie znaleziono testu'], 404);
        }
        if ($f == 'noquestions') {
            $test->questions = [];
        } else {
            $test->questions = UserGameParticipantAnswer::full($test, $f);
        }
        $today = date('Y-m-d') == date('Y-m-d', strtotime($test->finished_at)) ? true : false;
        return response()->json(['game_participant' => $test, 'today' => $today]);
    }
}
