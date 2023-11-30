@component('mail::message')
@lang('email.GREETING')

</br>

@lang("email.{$transaction->status}_{$transaction->type}_TRANSACTION")


@component('mail::table')
    |               |               |
    | ------------- |:-------------:|
    | @lang('email.attributes.ACCOUNT')  | {{ $user->email }}   |
    | @lang('email.attributes.AMOUNT')  | {{ $transaction->amount }} {{$transaction->coin->code}}   |
    | @lang('email.attributes.TYPE')  | @lang("email.attributes.{$transaction->type}")  |
    | @lang('email.attributes.DATE')   | {{ \Illuminate\Support\Facades\App::isLocale('fa') ? \Morilog\Jalali\Jalalian::fromCarbon($transaction->updated_at->setTimezone('Asia/Tehran')) :  $transaction->updated_at  }} |
@endcomponent

@lang('email.NOTICE')
@endcomponent
