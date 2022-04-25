<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\ApiQuestionsController;
use App\LawDocument;


class CheckQuestion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:legal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is for check question is out of date';

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
        $this->info('start!');
        $result = ApiQuestionsController::checkAll();
        $this->info('end!');
        return $result;

    }
}
