<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Logic\PmcTestLogic;
use Illuminate\Http\Request;

class PmcTestController extends Controller
{
    public function __construct()
    {
    }

    public function GenerarTokenSeguridad(Request $request)
    {
        $response = PmcTestLogic::GenerarTokenSeguridad();
        return $response;
    }

    public function CasesRecordsLists(Request $request)
    {
        $response = new ApiResponse();
        try {
            $response = PmcTestLogic::CasesRecordsLists();
            $error = $response[2];
            $message = $response[1];
        } catch (\Exception $e) {
            return ApiResponse::error('Error' . $e, 404, $response);
        }
        return ApiResponse::success($message, 200, $response, $error);
    }

    public function GetCaseWithRecordId(Request $request)
    {
        $response = new ApiResponse();
        try {
            $response = PmcTestLogic::GetCaseWithRecordId();
            $error = $response[2];
            $message = $response[1];
        } catch (\Exception $e) {
            return ApiResponse::error('Error' . $e, 404, $response);
        }
        return ApiResponse::success($message, 200, $response, $error);
    }

    public function InsertAllCaseWithRecordId(Request $request)
    {
        $response = new ApiResponse();
        try {
            $response = PmcTestLogic::InsertAllCaseWithRecordId();
            $error = $response[2];
            $message = $response[1];
        } catch (\Exception $e) {
            return ApiResponse::error('Error' . $e, 404, $response);
        }
        return ApiResponse::success($message, 200, $response, $error);
    }
    public function UpdateAllCaseWithRecordId(Request $request)
    {
        $response = new ApiResponse();
        try {
            $response = PmcTestLogic::UpdateAllCaseWithRecordId();
            $error = $response[2];
            $message = $response[1];
        } catch (\Exception $e) {
            return ApiResponse::error('Error' . $e, 404, $response);
        }
        return ApiResponse::success($message, 200, $response, $error);
    }


    public function ProbandoTabla(Request $request)
    {
        $response = PmcTestLogic::ProbandoTabla();
        return $response;
    }

    public function InsertRecordsCabClaims(Request $request)
    {
        $response = new ApiResponse();
        try {
            $response = PmcTestLogic::InsertRecordsCabClaims();
            $error = $response[2];
            $message = $response[1];
        } catch (\Exception $e) {
            return ApiResponse::error('Error' . $e, 404, $response);
        }
        return ApiResponse::success($message, 200, $response, $error);
    }

    public function InsertRecordsDetClaims(Request $request)
    {
        $response = new ApiResponse();
        try {
            $response = PmcTestLogic::InsertRecordsDetClaims();
            $error = $response[2];
            $message = $response[1];
        } catch (\Exception $e) {
            return ApiResponse::error('Error' . $e, 404, $response);
        }
        return ApiResponse::success($message, 200, $response, $error);
    }

    public function UpdateRecordsDetClaims(Request $request)
    {        
        $response = new ApiResponse();
        try {
            $response = PmcTestLogic::UpdateRecordsDetClaims();
            $error = $response[2];
            $message = $response[1];
        } catch (\Exception $e) {
            return ApiResponse::error('Error' . $e, 404, $response);
        }
        return ApiResponse::success($message, 200, $response, $error);
    }

    public function InsertRecordsCabClaimsCollections(Request $request)
    {
        $response = new ApiResponse();
        try {
            $response = PmcTestLogic::InsertRecordsCabClaimsCollections();
            $error = $response[2];
            $message = $response[1];
        } catch (\Exception $e) {
            return ApiResponse::error('Error' . $e, 404, $response);
        }
        return ApiResponse::success($message, 200, $response, $error);
    }

    public function InsertRecordsDetClaimsCollections(Request $request)
    {
        $response = new ApiResponse();
        try {
            $response = PmcTestLogic::InsertRecordsDetClaimsCollections();
            $error = $response[2];
            $message = $response[1];
        } catch (\Exception $e) {
            return ApiResponse::error('Error' . $e, 404, $response);
        }
        return ApiResponse::success($message, 200, $response, $error);
    }
    public function UpdateRecordsDetClaimsCollections(Request $request)
    {
        $response = new ApiResponse();
        try {
            $response = PmcTestLogic::UpdateRecordsDetClaimsCollections();
            $error = $response[2];
            $message = $response[1];
        } catch (\Exception $e) {
            return ApiResponse::error('Error' . $e, 404, $response);
        }
        return ApiResponse::success($message, 200, $response, $error);
    }
}
