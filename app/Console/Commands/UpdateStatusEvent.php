<?php

namespace App\Console\Commands;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateStatusEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:event-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status event in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Event::whereRaw("TIMESTAMP(registration_end_date, '23:59:00') <= ?", [Carbon::now()])
            ->where('status', 'upcoming')
            ->update(['status' => 'ongoing']);

        Event::whereRaw("TIMESTAMP(end_date, '23:59:00') <= ?", [Carbon::now()])
            ->where('status', 'ongoing')
            ->update(['status' => 'completed']);

        $this->info('Event statuses updated successfully.');
    }
}
