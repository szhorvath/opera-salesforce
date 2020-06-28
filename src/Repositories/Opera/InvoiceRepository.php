<?php

namespace Szhorvath\OperaSalesforce\Repositories\Opera;

use Szhorvath\OperaSalesforce\Traits\OperaRepositoryTrait;

class InvoiceRepository
{
    use OperaRepositoryTrait;

    public function find(string $invoiceNumber, string $account)
    {
        $sql = "SELECT * FROM stran
                WHERE st_trtype='I'
                AND st_trref = '$invoiceNumber'
                AND st_delacc = '$account'";

        return $this->foxproDB->query($sql)->first();
    }
}
