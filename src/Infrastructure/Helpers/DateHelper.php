<?php

namespace Ebolution\Core\Infrastructure\Helpers;

use Carbon\Carbon;

trait DateHelper
{
    public function getNow(): string
    {
        return Carbon::now()->toTimeString();
    }
    public function getNowToString($returnFormat='string'): string
    {
        if($returnFormat==='string')return (string) Carbon::now();
        else return Carbon::now();
    }

    public function getTimeDifference($startTime,$finishTime)
    {
        $startTime = new Carbon($startTime);
        $finishTime = new Carbon($finishTime);
        return $finishTime->diff($startTime)->format('%H:%I:%S');
    }
}
