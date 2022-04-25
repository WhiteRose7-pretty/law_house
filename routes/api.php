<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// default stuff from Laravel examples
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Auth::routes(['verify' => true]);

Route::prefix('admin')->group(function(){
    Route::middleware('check.hash.admin')->group(function(){

        Route::post('users', 'Admin\ApiUsersController@users');
        Route::post('users/remove', 'Admin\ApiUsersController@usersRemove');
        Route::post('users/type', 'Admin\ApiUsersController@usersType');

        Route::post('user-packages', 'Admin\ApiPackagesController@userPackages');
        Route::post('user-transactions', 'Admin\ApiPackagesController@userTransactions');
        Route::post('transactions', 'Admin\ApiPackagesController@transactions');


    });
    Route::middleware('check.hash.subadmin')->group(function(){
        Route::post('discounts', 'Admin\ApiPackagesController@discounts');
        Route::post('discounts/add', 'Admin\ApiPackagesController@discountsAdd');

        Route::post('packages', 'Admin\ApiPackagesController@packages');
        Route::post('packages/update', 'Admin\ApiPackagesController@update');
        Route::post('packages/remove', 'Admin\ApiPackagesController@remove');


        Route::post('tests', 'Admin\ApiTestsController@tests');
        Route::post('tests/update', 'Admin\ApiTestsController@update');
        Route::post('tests/remove', 'Admin\ApiTestsController@remove');


        Route::post('emails', 'Admin\ApiTestsController@emails');
        Route::post('send-email', 'Admin\ApiTestsController@send_email');

        Route::post('games/update-name', 'App\ApiGamesController@update_name');
    });
    Route::middleware('check.hash.editor')->group(function(){
        Route::post('content', 'Admin\ApiContentController@content');
        Route::post('content/image/upload','Admin\ApiContentController@contentImageUpload');
        Route::post('content/image/remove','Admin\ApiContentController@contentImageRemove');
        Route::post('content/list', 'Admin\ApiContentController@contentList');
        Route::post('content/remove', 'Admin\ApiContentController@contentRemove');
        Route::post('content/update', 'Admin\ApiContentController@contentUpdate');

        Route::post('document', 'Admin\ApiLawController@document');
        Route::post('document/element/update', 'Admin\ApiLawController@elementUpdate');
        Route::post('document/element/update_audio', 'Admin\ApiLawController@elementUpdateAudio');
        Route::post('document/elements', 'Admin\ApiLawController@elements');
        Route::post('document/elements_chapter', 'Admin\ApiLawController@elements_chapter');
        Route::post('document/element/remove', 'Admin\ApiLawController@elementRemove');
        Route::post('document/remove', 'Admin\ApiLawController@documentRemove');
        Route::post('document/update', 'Admin\ApiLawController@documentUpdate');
        Route::post('documents/list', 'Admin\ApiLawController@documentsList');
        Route::post('documents/all', 'Admin\ApiLawController@documentsListAll');
        Route::post('documents/chapter_all', 'Admin\ApiLawController@documentsListAllChapter');
        Route::post('document/download', 'Admin\ApiLawController@download');

        Route::post('questions/set', 'Admin\ApiQuestionsController@questionsSet');
        Route::post('questions/sets/list', 'Admin\ApiQuestionsController@questionsSetsList');
        Route::post('questions/sets/list/all', 'Admin\ApiQuestionsController@questionsSetsListAll');
        Route::post('questions/sets/remove', 'Admin\ApiQuestionsController@questionsSetsRemove');
        Route::post('questions/sets/update', 'Admin\ApiQuestionsController@questionsSetsUpdate');
        Route::post('questions/sets/repeat-enable', 'Admin\ApiQuestionsController@repeat_enable');

        Route::post('questions', 'Admin\ApiQuestionsController@questions');
        Route::post('question/remove', 'Admin\ApiQuestionsController@questionRemove');
        Route::post('question/update', 'Admin\ApiQuestionsController@questionUpdate');

        Route::post('games/new', 'App\ApiGamesController@adminGameNew');
    });
    Route::middleware('check.hash.user')->group(function(){
        Route::post('document/elements_chapter', 'Admin\ApiLawController@elements_chapter');
    });
});

Route::prefix('app')->group(function(){

    Route::post('packages/buy/status', 'App\ApiPackagesController@buyStatus');

    Route::middleware('check.hash.user')->group(function(){
        Route::post('user', 'App\ApiUserController@user');
        Route::post('user/update', 'App\ApiUserController@userUpdate');
        Route::post('user/password', 'App\ApiUserController@userPasswordUpdate');
        Route::post('user/remove', 'App\ApiUserController@remove');
        Route::post('user/email', 'App\ApiUserController@email');

        Route::post('games/ban', 'App\ApiGamesController@ban');
        Route::post('games/exam/answers', 'App\ApiGamesController@examAnswers');
        Route::post('games/exam/continue', 'App\ApiGamesController@examContinue');
        Route::post('games/race/answers', 'App\ApiGamesController@raceAnswers');
        Route::post('games/race/continue', 'App\ApiGamesController@raceContinue');
        Route::post('games/race/participants', 'App\ApiGamesController@raceParticipants');
        Route::post('games/join', 'App\ApiGamesController@join');
        Route::post('games/finish', 'App\ApiGamesController@finish');
        Route::post('games/finish/checkAll', 'App\ApiGamesController@finishCheckAll');
        Route::post('games/forceFinish', 'App\ApiGamesController@forceFinish');
        Route::post('games/hostOnly', 'App\ApiGamesController@hostOnly');
        Route::post('games/new', 'App\ApiGamesController@new');
        Route::post('games/leave', 'App\ApiGamesController@leave');
        Route::post('games/list', 'App\ApiGamesController@list');
        Route::post('games/finished', 'App\ApiGamesController@finished');
        Route::post('games/ready', 'App\ApiGamesController@ready');
        Route::post('games/start', 'App\ApiGamesController@start');
        Route::post('games/unflag', 'App\ApiGamesController@unflag');
        Route::post('games/view/exam', 'App\ApiGamesController@viewExam');
        Route::post('games/view/race', 'App\ApiGamesController@viewRace');
        Route::post('games/regulations', 'App\ApiGamesController@regulations');
        Route::post('games/summary', 'App\ApiGamesController@summary');

        Route::post('regulations','ContentController@regulations');

        Route::post('packages/buy/start', 'App\ApiPackagesController@buyStart');
        Route::post('packages/available', 'App\ApiPackagesController@available');
        Route::post('packages/form', 'App\ApiPackagesController@form');
        Route::post('packages/current', 'App\ApiPackagesController@current');
        Route::post('packages/quickfix', 'App\ApiPackagesController@quickfix');
        Route::post('packages/add/free/', 'App\ApiPackagesController@addFree');

        Route::post('transactions', 'App\ApiPackagesController@transactions');
        Route::post('transactions/return', 'App\ApiPackagesController@transactionsReturn');

        Route::post('questions/available','App\ApiQuestionsController@available');
        Route::post('questions/repeats/calendar','App\ApiQuestionsController@calendar');
        Route::post('questions/repeats/daily','App\ApiPackagesController@repeats');
        Route::post('questions/repeats/make','App\ApiTestsController@repeat');
        Route::post('questions/repeats/postpone','App\ApiQuestionsController@postpone');
        Route::post('questions/repeats/skip','App\ApiQuestionsController@skip');


        Route::post('questions/sets/available', 'App\ApiPackagesController@setsAvailable');
        Route::post('questions/sets/tree', 'App\ApiPackagesController@setsTree');



        Route::post('stats/daily','App\ApiStatsController@daily');
        Route::post('stats/summary','App\ApiStatsController@summary');

        Route::post('tests/answers','App\ApiTestsController@answers');
        Route::post('tests/continue','App\ApiTestsController@continue');
        Route::post('tests/delete','App\ApiTestsController@delete');
        Route::post('tests/end','App\ApiTestsController@end');
        Route::post('tests/finished','App\ApiTestsController@finished');
        Route::post('tests/new','App\ApiTestsController@new');
        Route::post('tests/run','App\ApiTestsController@run');
        Route::post('tests/summary','App\ApiTestsController@summary');
        Route::post('tests/calculate_questions','App\ApiTestsController@calculate_questions');

        Route::post('admin-tests/available', 'Admin\ApiTestsController@available');


    });
});

Route::post('/hash', 'Auth\HashController@verify');
