<?php

namespace Szhorvath\OperaSalesforce\Traits;

use Szhorvath\FoxproDB\FoxproDB;

trait OperaRepositoryTrait
{
    protected $foxproDB;

    /**
     * Create a new foxpro instance.
     *
     * @return void
     */
    public function __construct(string $source, string $mode = 'Read', $audit = true)
    {
        $this->foxproDB = new FoxproDB([
            'provider' => 'VFPOLEDB.1',
            'source'   => $source,
            'mode'     => $mode,
            'audit'    => $audit,
        ]);
    }
}
