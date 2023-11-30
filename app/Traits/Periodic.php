<?php


namespace App\Traits;


use App\Models\Market;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait Periodic
{
    public function scopeHourly($query)
    {
        return $query->selectRaw(DB::raw('sum(amount), count(id), DATE_FORMAT(created_at, "%Y-%m-%d %H") as date'))
            ->groupByRaw('date');
    }

    public function scopeDaily($query)
    {
        return $query->selectRaw(DB::raw('sum(amount),count(id), DATE_FORMAT(created_at, "%Y-%m-%d") as date'))
            ->groupByRaw('date');
    }

    public function scopeMonthly($query, $item = 'sum(amount)')
    {
        return $query->selectRaw(DB::raw($item . ',DATE_FORMAT(created_at, "%Y-%m") as date'))
            ->groupByRaw('date');
    }


    public function scopeAbstract($query, Carbon $now, Carbon $H, Market $market = null)
    {
        return $query->where('created_at', '<', $now->startOfDay())
            ->where('created_at', '>', $H->startOfDay())
            ->when(isset($market), function ($query) use ($market) {
               $query->whereHas('order.market', function ($query) use ($market) {
                   $query->where('id', $market->id);
               });
            });
    }
}
