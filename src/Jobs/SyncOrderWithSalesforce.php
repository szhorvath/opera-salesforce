<?php

namespace Szhorvath\OperaSalesforce\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Szhorvath\OperaSalesforce\Facades\OperaSalesforce;

class SyncOrderWithSalesforce implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $docNumber;

    protected $division;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($docNumber, $division)
    {
        $this->docNumber = $docNumber;
        $this->division = $division;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $regions = config('opera_salesforce.regions');
        $config = $regions[$this->division];

        $operaSalesforce = OperaSalesforce::setConfig($config)->init($this->docNumber);

        if ($operaSalesforce->operaOrderExists()) {
            $operaSalesforce->syncSalesforceWithOpera();
        }

        if (!$operaSalesforce->operaOrderExists() && $operaSalesforce->salesforceOrderExists()) {
            $operaSalesforce->deleteSalesforceOrder();
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        Log::alert($exception->getMessage(), [
            'docNumber' => $this->docNumber,
            'division' => $this->division,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
