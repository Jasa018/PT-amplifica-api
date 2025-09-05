<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SyncService;

class SyncStores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:stores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize products and orders for all active stores from the database';

    /**
     * Execute the console command.
     *
     * @param \App\Services\SyncService $syncService
     * @return int
     */
    public function handle(SyncService $syncService)
    {
        $this->info('Starting store synchronization...');

        $syncService->syncAllStores();

        $this->info('Synchronization complete.');

        return Command::SUCCESS;
    }
}
