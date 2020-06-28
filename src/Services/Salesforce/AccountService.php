<?php

namespace Szhorvath\OperaSalesforce\Services\Salesforce;

use Szhorvath\OperaSalesforce\Repositories\Salesforce\AccountRepository;


class AccountService
{
    protected $accountRepository;

    protected $account;

    public function __construct(array $config, string $accountCode = null)
    {
        $this->accountRepository = new AccountRepository($config);

        if ($accountCode) {
            $this->findAccount($accountCode);
        }
    }

    public function findAccount($accountCode)
    {
        if (!$this->account = $this->accountRepository->find($accountCode)) {
            $this->account = $this->accountRepository->getHolding();
        };
    }


    public function describe()
    {
        return $this->accountRepository->describe();
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function getAccountId()
    {
        return $this->account->Id;
    }

    public function isEmpty()
    {
        return empty($this->account);
    }
}
