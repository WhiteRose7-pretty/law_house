<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewUsersGamesRaceSummary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::statement("
          CREATE VIEW
              users_games_race_summary
          AS SELECT
              if(g.root_game_id,g.root_game_id,g.id) as user_game_id,
              p.user_id,
              p.name,
              sum(questions_answered_correct_count) as questions_answered_correct_count
          FROM
              users_games g
              join users_games_participants p on g.id = p.user_game_id
          GROUP BY
                if(g.root_game_id,g.root_game_id,g.id), p.user_id, p.name
      ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      DB::statement("
          DROP VIEW users_games_race_summary
      ");
    }
}
