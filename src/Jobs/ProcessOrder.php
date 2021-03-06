<?php

namespace Szhorvath\OperaSalesforce\Jobs;

use Exception;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use App\Models\Opera\OperaActivity;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Szhorvath\OperaSalesforce\Facades\OperaSalesforce;

class ProcessOrder implements ShouldQueue
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

        $operaSalesforce = OperaSalesforce::setConfig($config)->init($this->activity->opera_key_field_value);
        // $operaSalesforce = OperaSalesforce::init('DOC125073'); //Credit note
        // $operaSalesforce = OperaSalesforce::init('DOC124666'); //Credit note Rebate
        // $operaSalesforce = OperaSalesforce::init('DOC118522'); //multi invoice
        // $operaSalesforce = OperaSalesforce::init('DOC125208'); //not exist

        if ($operaSalesforce->operaOrderExists()) {
            $operaSalesforce->syncSalesforceWithOpera();
            $this->activity->cache = json_encode($operaSalesforce->getOperaOrder());
        }

        if (!$operaSalesforce->operaOrderExists() && $operaSalesforce->salesforceOrderExists()) {
            $operaSalesforce->deleteSalesforceOrder();
        }

        $this->activity->processing = false;
        $this->activity->processed_at = Carbon::now();
        $this->activity->save();
    }

    /**
     * The job failed to process.
     *
     * @param  Exception   $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        Log::alert($exception->getMessage(), [
            'docNumber' => $this->activity->opera_key_field_value,
            'division' => $this->activity->division,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
