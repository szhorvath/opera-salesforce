<?php

namespace Szhorvath\OperaSalesforce\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;

trait OperaServiceTrait
{
    protected $locale = 'en_GB';

    protected $currency = 'GBP';

    protected $office = 'UK';

    protected $dps = [
        '' => 100,
        0 => 1,
        1 => 10,
        2 => 100,
        3 => 1000,
        4 => 1000,
        5 => 10000
    ];

    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    public function setOffice($office)
    {
        $this->office = $office;
        return $this;
    }

    protected function getDecimalPlace($dps)
    {
        if (is_null($dps)) {
            return 100;
        }
        return $this->dps[$dps];
    }


    protected function getOperaQuantity($qty, $dps)
    {
        return (float) $qty / $this->getDecimalPlace($dps);
    }

    protected function getProductFamily($code)
    {
        $codeMap = collect([
            '0001' => 'Temporary Fencing',
            '0002' => 'Pedestrian Barriers',
            '0003' => 'Temporary Fencing',
            '0004' => 'Temporary Fencing',
            '0005' => 'Miscellaneous',
            '0006' => 'Temporary Fencing',
            '0007' => 'Temporary Fencing',
            '0008' => 'Temporary Fencing',
            '0010' => 'Pedestrian Barriers',
            '0015' => 'Accessories',
            '0020' => 'Pedestrian Barriers',
            '0030' => 'Temporary Fencing',
            '0040' => 'Temporary Fencing',
            '0050' => 'Accessories',
            '0055' => 'Print Services',
            '0060' => 'Accessories',
            '0070' => 'Accessories',
            '0080' => 'Temporary Hoarding',
            '0090' => 'Temporary Hoarding',
            '0090' => 'Temporary Hoarding',
            '0100' => 'Temporary Hoarding',
        ]);

        if (!$family = $codeMap->get($code)) {
            return 'Miscellaneous';
        }

        return $family;
    }


    protected function getOrderStatus($statusCode)
    {
        $statusList = collect([
            'Q' => 'Quote',
            'P' => 'Proforma',
            'O' => 'Order',
            'D' => 'Delivery',
            'I' => 'Complete',
            'C' => 'Complete',
        ]);

        return $statusList->get($statusCode);
    }

    protected function isoDate($date)
    {
        $format = Str::contains($date, '/') ? 'd/m/Y' : 'd-m-Y';
        try {
            return Carbon::createFromFormat($format, $date)->toISOString();
        } catch (\Throwable $th) {
            return null;
        }
    }

    protected function formatNumber($num)
    {
        return (new \NumberFormatter($this->locale, \NumberFormatter::DECIMAL))->parse($num);
    }

    public function getManagingOffice()
    {
        return $this->office;
    }
}
