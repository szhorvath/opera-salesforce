<?php

namespace Szhorvath\OperaSalesforce\Repositories\Opera;

use Szhorvath\OperaSalesforce\Traits\OperaRepositoryTrait;


class OrderRepository
{
    use OperaRepositoryTrait;


    public function find(string $docNumber)
    {
        $sql = "SELECT * FROM ihead WHERE ih_doc='$docNumber'";

        return $this->foxproDB->query($sql)->first();
    }
}
