<?php

use App\Http\Controllers\AbilityController;
use App\Http\Controllers\AboutUsCoWorkersController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BinanceConfigController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CheckCryptoDepositedController;
use App\Http\Controllers\CheckLoginOtpController;
use App\Http\Controllers\CoinController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ConstantFeeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\CryptoNetworkController;
use App\Http\Controllers\DepositAddressController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DollarPriceController;
use App\Http\Controllers\DustCreditController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FinnotechController;
use App\Http\Controllers\ForgetPasswordController;
use App\Http\Controllers\Google2faController;
use App\Http\Controllers\GuidPageController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnlinePaymentPortalController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RankController;
use App\Http\Controllers\RealTimePriceController;
use App\Http\Controllers\ReferralCodeController;
use App\Http\Controllers\ReferringUserController;
use App\Http\Controllers\RegisterEmail;
use App\Http\Controllers\RegisterPassword;
use App\Http\Controllers\ResendOptController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SendCodeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SiteInfoController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserAccountController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDocumentController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserStatusController;
use App\Http\Controllers\UserTransactionController;
use App\Http\Controllers\VandarController;
use App\Http\Controllers\WeeklyPriceController;
use App\Http\Middleware\TransactionMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// user register
Route::post('/signup', [RegisterEmail::class, 'store'])->name('userSignup');
Route::post('/confirm-email', [RegisterEmail::class, 'update']);
Route::post('/password', [RegisterPassword::class, 'store']);
Route::post('/signin', [LoginController::class, 'index'])->name('userSignin');
Route::post('/forget-password', [ForgetPasswordController::class, 'show']);
Route::post('/forget-password-confirm', [ForgetPasswordController::class, 'update']);
Route::post('/change-password', [ChangePasswordController::class, 'index']);
Route::post('/send-code', [SendCodeController::class, 'store']);

Route::post('/check-otp', [CheckLoginOtpController::class, 'store']);
Route::post('/resend-otp', [ResendOptController::class, 'index']);

Route::post('/admin-login', [AdminLoginController::class, 'store'])
    ->name('adminLogin');


Route::middleware(['auth:sanctum', 'blocked-user'])->group(function () {


    Route::middleware('user:user')->group(function () {

        Route::get('/users/init', [LoginController::class, 'show'])
            ->name('user-init');
        Route::delete('/users/logout', [LoginController::class, 'destroy']);


        Route::get('/authentication', [AuthenticationController::class, 'index'])
            ->name('profile');
        Route::post('/authentication', [AuthenticationController::class, 'store']);
        Route::put('/authentication', [AuthenticationController::class, 'update']);
        Route::get('/documents', [DocumentController::class, 'index']);
        Route::post('/documents', [DocumentController::class, 'store']);
        Route::get('/accounts', [AccountController::class, 'index']);
        Route::post('/accounts', [AccountController::class, 'store']);
        Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])
            ->middleware('can:delete,account');
        Route::patch('/accounts/{account}', [AccountController::class, 'update'])
            ->middleware('can:update,account');
        Route::post('/settings/password', [SettingController::class, 'password']);
        Route::get('/settings/google-2fa', [Google2faController::class, 'store']);
        Route::post('/settings/google-2fa/verify', [Google2faController::class, 'verify']);
        Route::post('/settings', [SettingController::class, 'store']);
        Route::post('/settings/otp', [SettingController::class, 'otp']);

        Route::get('/settings', [SettingController::class, 'index']);
        Route::get('/settings/{key}', [SettingController::class, 'show']);

        Route::get('/referrals', [ReferralCodeController::class, 'index'])
            ->middleware(['accepted-user']);

        Route::post('/transactions/type/{type}', [TransactionController::class, 'store'])
            ->where(['type' => 'deposit|withdraw'])
            ->middleware(['accepted-user', TransactionMiddleware::class]);

        Route::post('/transactions/otp', [TransactionController::class, 'otp'])
            ->middleware(['accepted-user']);

        Route::patch('/transactions/{transaction}/tx-id', [TransactionController::class, 'addTxId'])
            ->middleware(['accepted-user', 'can:update,transaction']);

        Route::post('/orders', [OrderController::class, 'store'])
            ->middleware(['accepted-user']);

        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])
            ->middleware(['accepted-user']);

        Route::get('credits', [CreditController::class, 'index']);
        Route::get('/credits/dust', [DustCreditController::class, 'index'])
            ->middleware(['accepted-user']);
        Route::post('/credits/dust', [DustCreditController::class, 'update'])
            ->middleware(['accepted-user']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/count', [NotificationController::class, 'count']);
        Route::patch('/notifications/{notification}', [NotificationController::class, 'patch']);

        Route::get('/referring-users', [ReferringUserController::class, 'index']);
        Route::get('/referring-users/count', [ReferringUserController::class, 'count']);
        Route::get('/referring-users/commission', [ReferringUserController::class, 'sumCommission']);
    });

    Route::middleware(['user:admin'])->group(function () {

        Route::get('/users/count', [UserController::class, 'count'])->middleware('can:ability,"LIST_USERS"');
        Route::post('/users/{user}/accept', [UserStatusController::class, 'accept'])->middleware('can:ability,"UPDATE_USERS"');
        Route::post('/users/{user}/reject', [UserStatusController::class, 'reject'])->middleware('can:ability,"UPDATE_USERS"');
        Route::get('/users/{user}/profile', [UserProfileController::class, 'show']);
        Route::patch('/users/{user}/profile', [UserProfileController::class, 'update']);
        Route::get('/users/{user}/documents', [UserDocumentController::class, 'index']);
        Route::post('/users/{user}/documents', [UserDocumentController::class, 'store']);
        Route::patch('/documents/{document}', [DocumentController::class, 'update']);
        Route::get('/users/{user}/accounts', [UserAccountController::class, 'index']);
        Route::post('/users/{user}/accounts', [UserAccountController::class, 'store']);
        Route::patch('/accounts/{account}/status', [AccountController::class, 'status']);
        Route::get('/users', [UserController::class, 'index'])->middleware(['can:ability,"LIST_USERS"']);
        Route::get('/users/{user}', [UserController::class, 'show'])->middleware('can:ability,"DETAILS_USERS"');
        Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('can:ability,"UPDATE_USERS"');
        Route::post('/users/{user}/notification', [UserNotificationController::class, 'store'])->middleware('can:ability,"CREATE_NOTIFICATIONS"');
        Route::post('/users/{user}/ban', [UserController::class, 'ban'])->middleware('can:ability,"UPDATE_USERS"');
        Route::post('/users/{user}/unban', [UserController::class, 'unban'])->middleware('can:ability,"UPDATE_USERS"');
        Route::patch('/users/{user}/settings', [UserController::class, 'setting'])->middleware('can:ability,"UPDATE_USERS"');

        Route::get('/users/{user}/finnotech', [FinnotechController::class, 'show']);

        Route::get('/admins/init', [AdminController::class, 'init']);
        Route::post('/admins/{admin}/activate', [AdminController::class, 'activate']);
        Route::post('/admins/{admin}/deactivate', [AdminController::class, 'deactivate']);
        Route::apiResource('/admins', AdminController::class);
        Route::post('/admins-logout', [AdminLoginController::class, 'destroy']);


        Route::get('/users/{user}/transactions', [UserTransactionController::class, 'index'])
            ->middleware('can:showAll,user');

        Route::get('/transactions/count', [TransactionController::class, 'count'])->middleware('can:ability,"LIST_WITHDRAW"');
        Route::post('/transactions/{transaction}/accept', [TransactionController::class, 'accept']);
        Route::post('/transactions/{transaction}/reject', [TransactionController::class, 'reject']);
        Route::post('/transactions/{transaction}/conflict', [TransactionController::class, 'conflict']);
        Route::post('/transactions/{transaction}/accept/auto', [TransactionController::class, 'acceptAuto']);

        Route::post('/transactions/{transaction}/check-toman-withdraw', [TransactionController::class, 'checkTomanWithdraw'])
            ->middleware('user:admin');
        Route::post('/psp/login', [VandarController::class, 'login']);

        Route::get('/orders/count', [OrderController::class, 'count'])->middleware('can:ability,"LIST_ORDERS"');
        Route::patch('/orders/{order}/{type}', [OrderController::class, 'update'])
            ->where(['type' => 'accept|reject']);

        Route::get('/trades', [TradeController::class, 'index']);
        Route::get('/trades/count', [TradeController::class, 'count'])->middleware('can:ability,"LIST_TRADES"');
        Route::get('/trades/gain/monthly', [TradeController::class, 'monthlyGain']);
        Route::get('/trades/markets/{market:name}/daily', [TradeController::class, 'daily']);
        Route::get('/trades/markets/{market:name}/monthly', [TradeController::class, 'monthly']);
        Route::get('/trades/markets/{market:name}/yearly', [TradeController::class, 'yearly']);
        Route::get('/trades/abstract', [TradeController::class, 'abstract'])->middleware('can:ability,"DETAILS_TRADES"');
        Route::get('/trades/markets/{market:name}/abstract', [TradeController::class, 'abstract'])->middleware('can:ability,"DETAILS_TRADES"');

        Route::get('/markets/gain', [MarketController::class, 'marketListWithGain']);
        Route::get('/markets/{market}/gain', [TradeController::class, 'gain']);
        Route::get('/markets/{market}/gain/monthly', [TradeController::class, 'monthlyGain']);

        Route::put('/coins/{coin}', [CoinController::class, 'update']);
        Route::patch('/coins/{coin}/activate', [CoinController::class, 'activate']);
        Route::patch('/coins/{coin}/deactivate', [CoinController::class, 'deactivate']);


        Route::post('/networks', [CryptoNetworkController::class, 'store']);
        Route::patch('/networks/{cryptoNetwork}', [CryptoNetworkController::class, 'update']);
        Route::post('/networks/{cryptoNetwork}/address', [CryptoNetworkController::class, 'updateAddress']);

        Route::post('/about-us', [AboutUsCoWorkersController::class, 'store']);
        Route::delete('/about-us/{option}', [AboutUsCoWorkersController::class, 'destroy']);

        Route::get('/site-info', [SiteInfoController::class, 'show']);
        Route::post('/site-info', [SiteInfoController::class, 'store']);

        Route::post('/configs', [ConfigController::class, 'store']);
        Route::get('/configs', [ConfigController::class, 'main']);
        Route::get('/configs/{type}/{key}', [ConfigController::class, 'show']);

        Route::post('/exchange/config', [BinanceConfigController::class, 'store'])->middleware('can:ability,"UPDATE_EXCHANGE_INFO"');

        Route::patch('/markets/{market}', [MarketController::class, 'update']);

        Route::post('/options', [OptionController::class, 'store']);
        Route::put('/options/{section}/{key}', [OptionController::class, 'update']);
        Route::delete('/options/{section}/{key}', [OptionController::class, 'destroy']);

        Route::get('/abilities', [AbilityController::class, 'index']);
        Route::apiResource('roles', RoleController::class);

        Route::apiResource('/ranks', RankController::class)
            ->only('store', 'update', 'destroy');

        Route::get('/dollar-prices', [DollarPriceController::class, 'index']);
        Route::post('/dollar-prices', [DollarPriceController::class, 'store']);

        Route::post('/constant-fee', [ConstantFeeController::class, 'store']);

        Route::apiResource('/faqs', FaqController::class)
            ->except('index');
        Route::post('/weekly-prices', [WeeklyPriceController::class, 'store']);

        Route::get('/users/{user}/referring-users', [ReferringUserController::class, 'index']);

        Route::apiResource('/pages', PageController::class)
            ->only(['update', 'store', 'destroy']);

        Route::post('/admin-account', [AdminAccountController::class, 'store']);
    });

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])
        ->middleware('can:show,transaction');


    Route::patch('/transactions/{transaction}/check-deposit', [CheckCryptoDepositedController::class, 'update'])
        ->middleware('can:update,transaction');

    Route::patch('/transactions/{transaction}/check-withdraw', [TransactionController::class, 'checkCryptoHasWithdrawn'])
        ->middleware('can:update,transaction');

    Route::get('/orders', [OrderController::class, 'index']);

    Route::get('/admin-account', [AdminAccountController::class, 'index']);

    Route::get('/finnotech', [FinnotechController::class, 'login']);
    Route::post('/finnotech/token', [FinnotechController::class, 'saveToken']);
    Route::get('/finnotech/token', [FinnotechController::class, 'token']);
    Route::get('/check-user', [LoginController::class, 'checkUser']);

    Route::get('/coins', [CoinController::class, 'index']);

    Route::get('/networks/{cryptoNetwork}/address', [CryptoNetworkController::class, 'address']);


    // todo must remove
    Route::get('/deposit-addresses/{coin}/{network}', [DepositAddressController::class, 'show']);
});

Route::get('/weekly-prices', [WeeklyPriceController::class, 'index']);
Route::get('/constant-fee', [ConstantFeeController::class, 'show']);
Route::get('/about-us', [AboutUsCoWorkersController::class, 'index']);


Route::get('markets/active-markets', [MarketController::class, 'activeCoin']);
Route::get('/markets/{market}/networks', [MarketController::class, 'network']);
Route::get('/markets/{market}', [MarketController::class, 'show']);

Route::apiResource('/contacts', ContactController::class)
    ->except('destroy', 'show');

Route::get('/ranks', [RankController::class, 'index']);

Route::get('/markets', [MarketController::class, 'index']);


Route::middleware(['auth:sanctum'])
    ->group(function () {
        Route::post('/admins-logout', [AdminLoginController::class, 'destroy']);
    });

Route::get('/coins/public', [CoinController::class, 'index']);

Route::get('/dollar-prices/last', [DollarPriceController::class, 'show']);
Route::get('/dollar-prices/tether', [DollarPriceController::class, 'tether']);

// todo must authorized not to add

//Route::post('/users/{user}/referrals', [ReferralController::class, 'store']);
//        Route::get('/users/{user}/referrals', [ReferralController::class, 'show']);

Route::get('/prices/realtime', [RealTimePriceController::class, 'index']);
Route::get('/prices/realtime/{coin}', [RealTimePriceController::class, 'show']);


//Route::get('/networks', [CryptoNetworkController::class, 'index']);
Route::get('/networks', [CryptoNetworkController::class, 'index']);
Route::get('/coins/{coin}/networks', [CryptoNetworkController::class, 'show']);




Route::apiResource('/statics/guid-page', GuidPageController::class);


Route::get('/options', [OptionController::class, 'index']);
Route::get('/options/{option?}', [OptionController::class, 'index']);
Route::get('/options/{section}/{key}', [OptionController::class, 'show']);

Route::apiResource('/pages', PageController::class)
    ->only(['index', 'show']);

Route::get('/faqs', [FaqController::class, 'index']);

Route::get('/online-payment/callback', [OnlinePaymentPortalController::class, 'update'])->name('portalCallback');
Route::get('/online-payment/{transaction}', [OnlinePaymentPortalController::class, 'store'])->name('portal');
