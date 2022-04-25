<?php

namespace App\Console\Commands;

use App\LawDocument;
use App\Question;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateQuestionsLawsText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iusvitae:questions-regenerate-laws-descriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Go Through All Questions and Fix All Laws Descriptions';

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

        $listLaws = LawDocument::listAllWithElementsAndContent([],true);

        $skipped = 0;
        $fixed = 0;
        $skip = 0;
        $limit = 100;
        do {
          DB::beginTransaction();
          $questions = $this->getQuestions($skip,$limit);
          if (empty($questions)) {
              continue;
          }
          foreach($questions as $q) {
              if (empty($q->laws_count)) {
                  if (empty($q->legal_basis_generated)) {
                      $skipped++;
                      continue;
                  }
                  $fixed++;
                  $q->legal_basis_generated = null;
                  $q->save();
                  continue;
              }
              $legal_basis_generated='';
              foreach($q->laws as $l) {
                  $legal_basis_generated.='<div><strong>';
                  $legal_basis_generated.= $listLaws[$l->law_document_id]['label'];
                  $legal_basis_generated.= ', ' . $listLaws[$l->law_document_id]['elements'][$l->law_document_element_id]['label'].'</strong><p>';
                  $legal_basis_generated.= $listLaws[$l->law_document_id]['elements'][$l->law_document_element_id]['content'];
                  $legal_basis_generated.='</p></div>';
              }
              $q->legal_basis_generated = $legal_basis_generated;
              $q->save();
              $fixed++;
          }
          $skip+=$limit;
          DB::commit();
        } while(count($questions) < $limit == false);

        $this->line('Fixed:'.$fixed);
        $this->line('Skipped:'.$skipped);
        $this->info('Done!');
    }

    protected function getQuestions($skip=0,$take=50) {
        return Question::with('laws')->withCount('laws')->orderBy('id')->skip($skip)->take($take)->get();
    }
}
