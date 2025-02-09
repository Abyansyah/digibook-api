<?php

namespace App\Console\Commands;

use App\Models\EventRegistration;
use App\Models\UserPoint;
use Illuminate\Console\Command;

class AddEventPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-event-points';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $registrations = EventRegistration::with('event')
            ->whereHas('event', function ($query) {
                $query->where('status', 'completed');
            })
            ->get();

        foreach ($registrations as $registration) {
            $alreadyAwarded = UserPoint::where('user_id', $registration->user_id)
                ->where('source_type', 'event')
                ->where('source_id', $registration->event_id)
                ->exists();

            if (!$alreadyAwarded) {
                UserPoint::create([
                    'user_id'     => $registration->user_id,
                    'source_type' => 'event',
                    'source_id'   => $registration->event_id,
                    'points'      => 10,
                ]);

                $this->info("Poin berhasil ditambahkan untuk user ID {$registration->user_id} pada event ID {$registration->event_id}");
            } else {
                $this->info("User ID {$registration->user_id} sudah mendapatkan poin untuk event ID {$registration->event_id}");
            }
        }

        return 0;
    }
}
