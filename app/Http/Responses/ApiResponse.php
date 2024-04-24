<?php
namespace App\Http\Responses;

class ApiResponse{

    public static function success($message='Success', $statusCode=200,$data=[], $error = false){
        return response()->json(
            [
                'message'    => $message,
                'statusCode' => $statusCode,
                'error'      => $error,
                'data'       => $data
            ],$statusCode
        );
    }

    public static function error($message='Error', $statusCode,$data=[]){
        return response()->json(
            [
                'message'    => $message,
                'statusCode' => $statusCode,
                 'error'     => true,
                 'data'      => $data
            ],$statusCode
        );
    }

    public static function object($data=[]){
        return response()->json(
            [                
                 'data'      => $data
            ]
        );
    }

}
