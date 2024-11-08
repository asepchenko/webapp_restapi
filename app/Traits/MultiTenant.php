<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait MultiTenant
{
    public static function bootMultiTenant()
    {
        if (!app()->runningInConsole()){//} && auth()->check()) {
            //if (auth()->user()) {
                static::addGlobalScope('branch_id', function (Builder $builder) {
                    $field = sprintf('%s.%s', $builder->getQuery()->from, 'branch_id');
                    $builder->where($field, auth()->user()->branch_id); //->orWhereNull($field);
                });
            //}
        }
    }
}
