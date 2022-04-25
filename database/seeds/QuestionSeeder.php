<?php

use App\QuestionsSet;
use App\Question;
use App\QuestionOption;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=1;$i<6;$i++) {
            $this->seedSet($i,1);
        }
        for($i=1;$i<5;$i++) {
            $this->seedSet($i,2);
        }
    }

    protected function seedSet($i,$j) {
        $qs = new QuestionsSet();
        $qs->name = "Zestaw nr $j.$i";
        $qs->group = "Grupa $j";
        $qs->save();
        $id = $qs->id;
        for($j=1;$j<101;$j++) {
            $this->seedQuestion($id,$j);
        }
    }

    protected function seedQuestion($qsid, $j) {
        $q = new Question();
        $q->questions_set_id = $qsid;
        $q->question = "Pytanie nr $j";
        $q->save();
        $c = ($j % 3)+1;
        for($i=1;$i<4;$i++) {
            $qo = new QuestionOption();
            $qo->question_id = $q->id;
            $qo->correct = $i==$c;
            $qo->option = "Odpowiedź $i";
            $qo->save();
        }
    }
}
