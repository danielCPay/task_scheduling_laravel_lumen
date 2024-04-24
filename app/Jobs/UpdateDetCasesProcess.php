<?php

namespace App\Jobs;

use App\Http\Responses\ApiResponse;
use App\Logic\PmcTestLogic;

class UpdateDetCasesProcess extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
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
}
