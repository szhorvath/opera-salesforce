<?php

namespace Szhorvath\OperaSalesforce\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use App\Models\Opera\OperaActivity;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Szhorvath\OperaSalesforce\Facades\OperaSalesforce;
use Throwable;

class ProcessProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $activity;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(OperaActivity $activity)
    {
        $this->activity = $activity;
        $this->activity->processing = true;
        $this->activity->save();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $regions = config('opera_salesforce.regions');
        $config = $regions[$this->activity->division];

        $product = OperaSalesforce::setConfig($config)->syncProductWithSalesforce($this->activity->opera_key_field_value);

        if ($product) {
            $this->activity->cache = json_encode($product->toArray());
        }

        $this->activity->processing = false;
        $this->activity->processed_at = Carbon::now();
        $this->activity->save();
    }

    /**
     * The job failed to process.
     *
     * @param  Throwable   $th
     * @return void
     */
    public function failed(Throwable $th)
    {
        Log::alert($th->getMessage(), [
            'invoiceId' => $this->activity->opera_key_field_value,
            'division' => $this->activity->division,
            'file' => $th->getFile(),
            'line' => $th->getLine(),
        ]);
    }
}
