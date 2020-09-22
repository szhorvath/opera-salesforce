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

class ProcessInvoice implements ShouldQueue
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
        $invoice = OperaSalesforce::initOperaInvoiceService(null, null, $this->activity->opera_id_field)->updateInvoice();

        if ($invoice) {
            $this->activity->cache = json_encode($invoice->toArray());
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
            'invoiceId' => $this->activity->opera_id_field,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
        throw new Exception($this->activity->opera_id_field . ' - ' . $exception->getMessage() . ' - ' . $exception->getFile() . ':' . $exception->getLine());
    }
}
