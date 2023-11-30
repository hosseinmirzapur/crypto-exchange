@component('mail::message')
@lang('email.GREETING')

</br>

{{  __("email.ACCEPTED_ORDER_TITLE", [
    'type' => trans("email.attributes.{$order->type}")
]) }}


@component('mail::table')
    |               |               |
    | ------------- |:-------------:|
    | @lang('email.attributes.ACCOUNT')  | {{ $user->email }}   |
    | @lang('email.attributes.AMOUNT')  | {{ (float) $order->amount }} {{$order->market->coin->code}}   |
    | @lang('email.attributes.TYPE')  | @lang("email.attributes.{$order->type}")  |
    | @lang('email.attributes.DATE')   | {{ \Illuminate\Support\Facades\App::isLocale('fa') ? \Morilog\Jalali\Jalalian::fromCarbon($order->updated_at->setTimezone('Asia/Tehran')) :  $order->updated_at }} |
@endcomponent

@lang('email.NOTICE')
@endcomponent
