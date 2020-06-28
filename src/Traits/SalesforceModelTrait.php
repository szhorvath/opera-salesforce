<?php

namespace Szhorvath\OperaSalesforce\Traits;

trait SalesforceModelTrait
{
    /**
     * Scope a query to only include managing office.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOffice($query, $code)
    {
        return $query->where('Managing_Office__c', $code);
    }

    /**
     * Scope a query to only include product of UK.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUk($query)
    {
        return $query->where('Managing_Office__c', 'UK');
    }

    /**
     * Scope a query to only include product of NL.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNl($query)
    {
        return $query->where('Managing_Office__c', 'NL');
    }

    /**
     * Scope a query to only include product of US.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUs($query)
    {
        return $query->where('Managing_Office__c', 'US');
    }

    /**
     * Scope a query to only include product of CE.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCe($query)
    {
        return $query->where('Managing_Office__c', 'CE');
    }
}
