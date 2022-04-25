<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class UserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iusvitae:user-admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make existing user an admin';

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
        $email = $this->argument('email');
        // echo "$email\n";
        $user = User::where('email',$email)->first();
        if (empty($user)) {
            $this->error("There is no user registered with email <{$email}>!");
            return;
        }
        $this->line("Found user registered under: {$email}");
        $this->line("  Current user account type: {$user->type}\n");
        if ($user->type == 'admin') {
            $this->error("This user is already admin!");
            return;
        }
        if ($this->confirm('This will set user level to admin, are you sure?')) {
            $user->type = 'admin';
            $user->save();
            $this->info('Fixed as requested!');
            return;
        }
        $this->line("Aborted!");
    }
}
