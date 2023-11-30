<?php

namespace Database\Seeders;

use App\Models\Coin;
use Illuminate\Database\Seeder;

class CoinSeeder extends Seeder
{
    const COINS = [
        'TOMAN' => 'تومان',
        'BTC' => 'بیت‌کوین',
        'LTC' => 'لایت‌کوین',
        'BCH' => 'بیت‌کوین‌کش',
        'ETH' => 'اتریوم',
        'XRP' => 'ریپل',
        'USDT' => 'تتر',
        'EOS' => 'ایس',
        'ADA' => 'کاردانو',
        'TRX' => 'ترون',
        'XMR' => 'مونرو',
        'NEO' => 'نئو',
        'MIOTA' => 'آیوتا',
        'DASH' => 'دَش',
        'ETC' => 'اتریوم کلاسیک',
        'DOGE' => 'داج‌ کوین',
        'ZEC' => 'زی ‌کش',
        'BNB' => 'بایننس‌کوین',
        'LINK' => 'چِین‌لینک',
        'XLM' => 'استلار',
        'DOT' => 'پولکادات',
        'CRO' => 'کریپتوکوین',
        'XTZ' => 'تزوس',
        'LEO' => 'لئو',
        'ATOM' => 'کازمس',
        'XEM' => 'ان‌ای‌ام',
        'HT' => 'هوبیتوکن',
        'VET' => 'وی‌چِین',
        'UNI' => 'یونی‌سواپ',
        'WAVES' => 'وِیوز',
        'QTUM' => 'کوانتوم',
        'ZIL' => 'زیلیکا',
        'DCR' => 'دِکِرِد',
        'AAVE' => 'آوی',
        'LUNA' => 'ترا',
        'FTT' => 'اف‌تی‌اکس',
        'SOL' => 'سولانا',
        'GRT' => 'گراف',
        'AVAX' => 'اولانچ',
        'SNX' => 'سینتتیکس',
        'FIL' => 'فایل‌کوین',
        'SUSHI' => 'سوشی',
        'SHIB' => 'شیبا',
        'MATIC' => 'پولیگان',
        'COMP' => 'کامپاند',
        'HOT' => 'هولو',
        'TOMO' => 'تومو',
        'FTM' => 'فانتوم',
        'REEF' => 'ریف',
        'DODO' => 'دودو',
        '1INCH' => 'وان‌اینچ'
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (static::COINS as $code => $label) {
            Coin::create(
                [
                    'code' => $code,
                    'name' => $code === 'TOMAN' ? 'toman' : '',
                    'label' => $label,
                    'status' => Coin::STATUS[0]
                ]
            );

        }

        $coins = Coin::all();
        $quote = Coin::where('code', 'TOMAN')->first();
        foreach ($coins as $coin) {
            if ($coin->code === 'TOMAN') {
                continue;
            }
            $coin->markets()
                ->create([
                    'name' => $coin->code . "TOMAN",
                    'quote_id' => $quote->id,
                    'price' => 0
                ]);
        }
        $quote = Coin::where('code', 'USDT')->first();
        foreach ($coins as $coin) {
            if ($coin->code === 'TOMAN' || $coin->code === 'USDT') {
                continue;
            }
            $coin->markets()
                ->create([
                    'name' => $coin->code . "USDT",
                    'quote_id' => $quote->id,
                    'price' => 0
                ]);
        }
    }
}
