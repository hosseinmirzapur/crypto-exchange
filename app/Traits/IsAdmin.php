<?php


namespace App\Traits;


use App\Models\Admin;

trait IsAdmin
{
    public function isAdmin()
    {
        return $this instanceof Admin;
    }
}
