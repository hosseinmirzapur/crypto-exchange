<?php


namespace App\Classes;


class Response
{
    private $response = [];

    private function globalResponse($status)
    {
        return response()->json($this->response);
    }


    public static function successResponse($data = [], $status = 200)
    {
        $response = new static();
        if (!empty($data)) {
            $response->$response['data'] = $data;
        }
        return $response->globalResponse($status);
    }

    public static function errorResponse($status = 200)
    {
        $response = new static();
        return $response->globalResponse($status);
    }

    public function withMessage($message) {
        $this->response['message'] = $message;
        return $this;
    }



}
