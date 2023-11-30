<?php

namespace App\Helpers;


/**
 * @return \Illuminate\Contracts\Auth\Authenticatable|null
 */
function current_user()
{
    return auth()->user();
}


function successResponse($data = null, $status = 200, $options = [])
{
    $response = ['type' => 'success', 'status' => $status];
    $response['message'] = $options['message'] ?? trans('messages.success');

    if (isset($data)) {
        $response['data'] = $data;
    }

    return response()->json(
        $response,
        $status);
}


function errorResponse($message, $status = 404, $code = null)
{
    $errorResponse = [
        'message' => $message,
        'type' => 'error',
        'status' => $status,
    ];

    if (isset($code)) {
        $errorResponse = array_merge($errorResponse, ['code' => $code]);
    }

    return response()->json(
        $errorResponse,
        $status);
}

function is_product_env()
{
    return env('APP_ENV') === 'production';
}

function customRound($value, $delimiter = 5)
{
    return round($value * 10 ** $delimiter) / (10 ** $delimiter);
}

function hasRemain($a, $b)
{
    return $a - ceil($a / $b) === 0;
}

function prettifyNumber(float $number, $round = -1)
{
    if ($number < 0.001) {
        return rtrim(rtrim(sprintf('%.8F', $number), '0'), ".");
    }
    if ($round > -1) {
        return customRound($number, $round);
    }
    return $number;
}
