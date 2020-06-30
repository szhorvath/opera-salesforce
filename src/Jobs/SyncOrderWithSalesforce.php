<?php

namespace Szhorvath\OperaSalesforce\Jobs;

use Exception;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use App\Models\Opera\OperaActivity;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Szhorvath\OperaSalesforce\Facades\OperaSalesforce;

class SyncOrderWithSalesforce implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $docNumber;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($docNumber)
    {
        $this->docNumber = $docNumber;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $operaSalesforce = OperaSalesforce::init($this->docNumber);

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
        throw new Exception($this->docNumber . ' - ' . $exception->getMessage());
    }
}
