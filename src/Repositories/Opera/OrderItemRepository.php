<?php

namespace Szhorvath\OperaSalesforce\Repositories\Opera;

use Szhorvath\OperaSalesforce\Traits\OperaRepositoryTrait;

class OrderItemRepository
{
    use OperaRepositoryTrait;

    public function getByDocNumber(string $docNumber)
    {
        $sql = "SELECT itran.*, cname.*, cf_dps
                FROM itran
                LEFT OUTER JOIN cname ON it_stock=cn_ref
                LEFT OUTER JOIN cfact ON cn_fact=cf_code
                WHERE it_doc='$docNumber'";

        return  $this->foxproDB->query($sql)->get();
    }

    public function getById(int $id)
    {
        $sql = "SELECT itran.*, cf_dps
                FROM itran
                LEFT OUTER JOIN cname ON it_stock=cn_ref
                LEFT OUTER JOIN cfact ON cn_fact=cf_code
                WHERE id=$id";

        return $this->foxproDB->query($sql)->first();
    }
}
