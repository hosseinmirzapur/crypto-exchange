<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Config;
use App\Models\DollarPrice;
use App\Models\Rank;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'email' => "mbahmanyar72@gmail.com",
            'password' => Hash::make(123456),
            "email_verified_at" => Carbon::now()->toDateTimeString()
        ]);


        $user->profile()
            ->create(
                [
                    "name" => "mohammad",
                    "national_code" => "123456",
                    "birthday" => "1993-04-10",
                    "address" => "ندارم",
                    "phone" => "09155212798",
                    "mobile" => "46546"
                ]
            );

        $user
            ->accounts()
            ->create(
                [
                    'card_number' => '1235464587963254',
                    'sheba' => '654646465465464644',
                    'account_number' => '56464646546654646'
                ]
            );
        $user
            ->accounts()
            ->create(
                [
                    'card_number' => '1235464564646464',
                    'sheba' => '654646411111111',
                    'account_number' => '564646123123646'
                ]
            );

        Rank::create([
            'name' => "SILVER",
            'label' => "نقره ای",
            'criterion' => 20000000,
            'fee' => 0.0035,
        ]);

        Rank::create([
            'name' => "GOLD",
            'label' => "طلایی",
            'fee' => 0.003,
            'criterion' => 50000000,
        ]);

        Rank::create([
            'name' => "PLATINUM",
            'label' => "پلاتینیوم",
            'fee' => 0.0025,
            'criterion' => 100000000,
        ]);

        Rank::create([
            'name' => "SPECIAL",
            'label' => "ویژه",
            'fee' => 0.002,
            'criterion' => 2147483647,
        ]);

        Admin::create([
            'name' => 'admin',
            'lastname' => 'admini',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
            'mobile' => '091563214566',
            'status' => 'ACTIVATED'
        ]);

        DollarPrice::create(
            [
                'price' => 25000,
                'changes' => 25000
            ]
        );

        Config::create([
            'type' => 'SCHEDULE',
            'key' => 'ALL_COINS',
            'value' => 0
        ]);

        Config::create([
            'type' => 'SCHEDULE',
            'key' => 'EXCHANGE_INFO',
            'value' => 0
        ]);

        Config::create([
            'type' => 'MAIN',
            'key' => 'AUTO_TRADE',
            'value' => 'AUTO'
        ]);

        $this->call(AbilitySeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(CoinSeeder::class);
        $this->call(PageSeeder::class);
    }
}
