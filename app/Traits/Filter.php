<?php


namespace App\Traits;


use Illuminate\Database\Eloquent\Builder;

trait Filter
{

    public function scopeStatus(Builder $query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeType(Builder $query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDate(Builder $query, $date)
    {
        if (!is_array($date) || !isset($date[1])) {
            return $query->whereDate('created_at', '=', $date);
        }
        return $query->whereDate('created_at', '>', $date[0])
            ->whereDate('created_at', '<', $date[1]);
    }


    public function scopePrice(Builder $query, $price)
    {
        if (!is_array($price) || !isset($price[1])) {
            return;
        }
        return $query->where('price', '>=', $price[0])
            ->where('price', '<=', $price[1]);
    }

    public function scopeWithFilter(Builder $query)
    {
        $filters = request()->query();
        foreach ($filters as $k => $v) {
            $m = "scope" . ucfirst($k);
            if (method_exists(Filter::class, $m)) {
                $query->$k($v);
            }
        }
        return $query;
    }

    /**
     * @param Builder $query
     * @param array $items $key => [$value, type = (value, range, list, dateValue, dateRange)]
     * @return Builder
     */
    public function scopeFilter(Builder $query, $items = [])
    {
        $dateItems = ['created_at', 'updated_at'];

        if (empty($items)) {
            return $query;
        }
        $filters = request()->query();

        foreach ($filters as $k => $v) {
            if (!in_array($k, $items)) {
                continue;
            }
            if (is_array($v)) {
                if (in_array($k, $dateItems)) {
                    $query->whereDate($k, '>=', $v[0])
                        ->whereDate($k, '<=', $v[1]);
                } elseif ($k === 'status') {
                    $query->whereIn($k, $v);
                }
                else {
                    $query->where($k, '>=', min($v))
                        ->where($k, '<=', max($v));
                }
            } else {
                if (in_array($k, $dateItems)) {
                    $query->whereDate($k, $v);
                } elseif ($k === 'created_atFrom') {
                    if (!isset($filters['created_atTo'])) {
                        $query->whereDate('created_at', $v);
                    } else {
                        $query->whereDate('created_at', '>=', $v);
                    }
                } elseif ($k === 'created_atTo') {
                    $query->whereDate('created_at', '<=', $v);
                } else {
                    if (in_array($k, ['email', 'name'])) {
                        $query->where($k,'like', $v);
                    } else {
                        $query->where($k, $v);
                    }
                }
            }
        }
        if (request()->has('orderBy')) {
            $query->orderBy(request('orderBy'), request('orderByType', 'asc'));
        }

        return $query;
    }

    public function scopeName(Builder $query, $type)
    {
        return $query->where('name', $type);
    }

    public function scopeEmail(Builder $query, $type)
    {
        return $query->where('email', $type);
    }

    public function scopeMobile(Builder $query, $type)
    {
        return $query->where('mobile', $type);
    }

    public function scopeIsBanned(Builder $query, $type)
    {
        return $query->where('is_banned', $type);
    }


}
