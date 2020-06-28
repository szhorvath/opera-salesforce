<?php

namespace Szhorvath\OperaSalesforce\Jobs;

use Carbon\Carbon;

use Illuminate\Bus\Queueable;
use App\Models\Opera\OperaActivity;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Szhorvath\OperaSalesforce\Jobs\ProcessOrder;

class ProcessOperaActivityLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            'opera',
            'log',
            'process',
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $unprocessedDocs = OperaActivity::where('opera_key_field_value', 'like', 'DOC%')
            ->where('processed_at', null)
            ->orderBy('processing')
            ->get()->groupBy('opera_key_field_value');

        $date = Carbon::now();

        $unprocessedDocs->each(function ($activities) use ($date) {
            $processing = $activities->filter(fn ($activity) => $activity->processing);

            //if is not processing than create the process
            if ($processing->isEmpty()) {
                $activity = $activities->sortByDesc('opera_created_at')->shift();
                ProcessOrder::dispatch($activity)->delay($date);
                $date->addSeconds(2);
            }

            //Delete rest of the log items;
            $activities->filter(fn ($activity) => !$activity->processing)
                ->each(fn ($activity) => $activity->delete());
        });
    }
}
