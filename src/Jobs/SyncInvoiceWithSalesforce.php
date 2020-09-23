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

class SyncInvoiceWithSalesforce implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoiceId;

    protected $division;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($invoiceId, $division)
    {
        $this->invoiceId = $invoiceId;
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

        OperaSalesforce::setConfig($config)->initOperaInvoiceService(null, null, $this->invoiceId)->updateInvoice();
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
            'invoiceId' => $this->invoiceId,
            'division' => $this->division,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
