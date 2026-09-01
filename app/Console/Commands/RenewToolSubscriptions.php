<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RenewToolSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tools:renew';
    protected $description = 'Automatically renew tool subscriptions by deducting credits from user wallets.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Tool subscription renewals are disabled (lifetime tool unlock model active).');

        return 0;
    }
}
