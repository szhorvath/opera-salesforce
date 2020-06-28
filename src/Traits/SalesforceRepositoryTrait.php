<?php

namespace Szhorvath\OperaSalesforce\Traits;


trait SalesforceRepositoryTrait
{
    protected $config;

    /**
     * Create a new foxpro instance.
     *
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
}
