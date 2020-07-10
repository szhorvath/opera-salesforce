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
use Szhorvath\OperaSalesforce\Jobs\ProcessInvoice;

class ProcessOperaActivityLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->date = Carbon::now();
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
        $this->handleOrders();
        $this->handleInvoices();
    }

    protected function handleOrders()
    {
        $unprocessedDocs = OperaActivity::where('opera_key_field_value', 'like', 'DOC%')
            ->where('processed_at', null)
            ->orderBy('processing')
            ->get()->groupBy('opera_key_field_value');

        $unprocessedDocs->each(function ($activities) {
            $processing = $activities->filter(fn ($activity) => $activity->processing);

            //if is not processing than create the process
            if ($processing->isEmpty()) {
                $activity = $activities->sortByDesc('opera_created_at')->shift();
                ProcessOrder::dispatch($activity)->delay($this->date);
                $this->date->addSeconds(5);
            }

            //Delete rest of the log items;
            $activities->filter(fn ($activity) => !$activity->processing)
                ->each(fn ($activity) => $activity->delete());
        });
    }
    protected function handleInvoices()
    {
        $unprocessedInvoices = OperaActivity::where('opera_table_name', 'STRAN')
            ->where('processed_at', null)
            ->orderBy('processing')
            ->get()->groupBy('opera_id_field');

        $date = Carbon::now();

        $unprocessedInvoices->each(function ($activities) {
            $processing = $activities->filter(fn ($activity) => $activity->processing);

            //if is not processing than create the process
            if ($processing->isEmpty()) {
                $activity = $activities->sortByDesc('opera_created_at')->shift();
                ProcessInvoice::dispatch($activity)->delay($this->date);
                $this->date->addSeconds(5);
            }

            //Delete rest of the log items;
            $activities->filter(fn ($activity) => !$activity->processing)
                ->each(fn ($activity) => $activity->delete());
        });
    }
}
