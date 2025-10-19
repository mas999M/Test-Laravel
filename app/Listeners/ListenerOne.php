<?php

namespace App\Listeners;

use App\Events\EventOne;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ListenerOne
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EventOne $event): void
    {
        Log::info('asdsadasa' , [
            "someData" => $event->someData
        ]);
    }
}
