<?php

namespace Szhorvath\OperaSalesforce\Repositories\Opera;

use Szhorvath\OperaSalesforce\Traits\OperaRepositoryTrait;

class ProductRepository
{
    use OperaRepositoryTrait;

    public function find(string $code)
    {
        $sql = "SELECT *
                FROM cname
                WHERE cn_ref='$code'";

        return $this->foxproDB->query($sql)->first();
    }
}
