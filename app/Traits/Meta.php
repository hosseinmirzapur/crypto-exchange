<?php


namespace App\Traits;


trait Meta
{
    public function addMeta($key, $value)
    {
        return $this->metas()
            ->create([
                'meta_key' => $key,
                'meta_value' => $value,
            ]);
    }
}
