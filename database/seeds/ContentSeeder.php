<?php

use App\Content;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Content::where('category','info')->count()>0) {
            $this->command->warn('Initial content has been already seeded. All Good But Aborting!');
            return;
        }

        $c = new Content();
        $c->category = 'info';
        $c->title = 'Polityka Prywatności';
        $c->title_uri = 'polityka-prywatnosci';
        $c->save();

        $c = new Content();
        $c->category = 'info';
        $c->title = 'Regulamin';
        $c->title_uri = 'regulamin';
        $c->save();

        $c = new Content();
        $c->category = 'info';
        $c->private = true;
        $c->title = 'Regulamin Administratora';
        $c->title_uri = 'regulamin-administratora';
        $c->save();

        $this->command->info('Seeded initial content entries!');
    }
}
