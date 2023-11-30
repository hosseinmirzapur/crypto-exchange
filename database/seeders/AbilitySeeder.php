<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('abilities')
            ->insert(
                [
                    ['name' => 'LIST_USERS', 'label' => 'LIST_USERS'],
                    ['name' => 'DETAILS_USERS', 'label' => 'DETAILS_USERS'],
                    ['name' => 'UPDATE_USERS', 'label' => 'UPDATE_USERS'],
                    ['name' => 'DELETE_USERS', 'label' => 'DELETE_USERS'],
                    ['name' => 'LIST_ADMINS', 'label' => 'LIST_ADMINS'],
                    ['name' => 'PROFILE_ADMINS', 'label' => 'PROFILE_ADMINS'],
                    ['name' => 'DETAILS_ADMINS', 'label' => 'DETAILS_ADMINS'],
                    ['name' => 'CREATE_ADMINS', 'label' => 'CREATE_ADMINS'],
                    ['name' => 'UPDATE_ADMINS', 'label' => 'UPDATE_ADMINS'],
                    ['name' => 'DELETE_ADMINS', 'label' => 'DELETE_ADMINS'],
                    ['name' => 'LIST_ROLES', 'label' => 'LIST_ROLES'],
                    ['name' => 'DETAILS_ROLES', 'label' => 'DETAILS_ROLES'],
                    ['name' => 'CREATE_ROLES', 'label' => 'CREATE_ROLES'],
                    ['name' => 'UPDATE_ROLES', 'label' => 'UPDATE_ROLES'],
                    ['name' => 'DELETE_ROLES', 'label' => 'DELETE_ROLES'],
                    ['name' => 'LIST_DOLLARS', 'label' => 'LIST_DOLLARS'],
                    ['name' => 'DETAILS_DOLLARS', 'label' => 'DETAILS_DOLLARS'],
                    ['name' => 'CREATE_DOLLARS', 'label' => 'CREATE_DOLLARS'],
                    ['name' => 'LIST_WALLET', 'label' => 'LIST_WALLET'],
                    ['name' => 'CREATE_WALLET_ADDRESS', 'label' => 'CREATE_WALLET_ADDRESS'],
                    ['name' => 'DETAILS_WALLET', 'label' => 'DETAILS_WALLET'],
                    ['name' => 'CREATE_WALLET', 'label' => 'CREATE_WALLET'],
                    ['name' => 'UPDATE_WALLET', 'label' => 'UPDATE_WALLET'],
                    ['name' => 'LIST_WITHDRAW', 'label' => 'LIST_WITHDRAW'],
                    ['name' => 'DETAILS_WITHDRAW', 'label' => 'DETAILS_WITHDRAW'],
                    ['name' => 'UPDATE_WITHDRAW', 'label' => 'UPDATE_WITHDRAW'],
                    ['name' => 'DELETE_WITHDRAW', 'label' => 'DELETE_WITHDRAW'],
                    ['name' => 'LIST_HISTORIES', 'label' => 'LIST_HISTORIES'],
                    ['name' => 'DETAILS_HISTORIES', 'label' => 'DETAILS_HISTORIES'],
                    ['name' => 'UPDATE_EXCHANGE_INFO', 'label' => 'UPDATE_EXCHANGE_INFO'],
                    ['name' => 'UPDATE_FAQ', 'label' => 'UPDATE_FAQ'],
                    ['name' => 'CREATE_FAQ', 'label' => 'CREATE_FAQ'],
                    ['name' => 'DELETE_FAQ', 'label' => 'DELETE_FAQ'],
                    ['name' => 'LIST_FAQ', 'label' => 'LIST_FAQ'],
                    ['name' => 'LIST_USER_MANUAL', 'label' => 'LIST_USER_MANUAL'],
                    ['name' => 'UPDATE_USER_MANUAL', 'label' => 'UPDATE_USER_MANUAL'],
                    ['name' => 'CREATE_USER_MANUAL', 'label' => 'CREATE_USER_MANUAL'],
                    ['name' => 'DELETE_USER_MANUAL', 'label' => 'DELETE_USER_MANUAL'],
                    ['name' => 'CREATE_MARKET', 'label' => 'CREATE_MARKET'],
                    ['name' => 'UPDATE_MARKET', 'label' => 'UPDATE_MARKET'],
                    ['name' => 'DELETE_MARKET', 'label' => 'DELETE_MARKET'],
                    ['name' => 'LIST_MARKET', 'label' => 'LIST_MARKET'],
                    ['name' => 'CREATE_ORDERS', 'label' => 'CREATE_ORDERS'],
                    ['name' => 'UPDATE_ORDERS', 'label' => 'UPDATE_ORDERS'],
                    ['name' => 'DELETE_ORDERS', 'label' => 'DELETE_ORDERS'],
                    ['name' => 'LIST_ORDERS', 'label' => 'LIST_ORDERS'],
                    ['name' => 'DETAILS_ORDERS', 'label' => 'DETAILS_ORDERS'],
                    ['name' => 'LIST_TRADES', 'label' => 'LIST_TRADES'],
                    ['name' => 'UPDATE_TRADES', 'label' => 'UPDATE_TRADES'],
                    ['name' => 'CREATE_TRANSACTIONS', 'label' => 'CREATE_TRANSACTIONS'],
                    ['name' => 'LIST_TRANSACTIONS', 'label' => 'LIST_TRANSACTIONS'],
                    ['name' => 'CREATE_NOTIFICATIONS', 'label' => 'CREATE_NOTIFICATIONS'],
                    ['name' => 'LIST_NOTIFICATIONS', 'label' => 'LIST_NOTIFICATIONS'],
                    ['name' => 'DETAILS_TRADES', 'label' => 'DETAILS_TRADES'],
                    ['name' => 'LIST_TICKETS', 'label' => 'LIST_TICKETS'],
                    ['name' => 'DETAILS_MARKET', 'label' => 'DETAILS_MARKET']
                ]
            );
    }
}

