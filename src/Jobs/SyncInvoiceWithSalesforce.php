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

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        OperaSalesforce::initOperaInvoiceService(null, null, $this->invoiceId)->updateInvoice();
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
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
        throw new Exception($this->invoiceId . ' - ' . $exception->getMessage() . ' - ' . $exception->getFile() . ':' . $exception->getLine());
    }
}
