<?php

namespace App\Logic;

use App\Models\PmcTestModel;
use App\Class\General;

class PmcTestLogic
{
    public static function GenerarTokenSeguridad()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');

        $token = '';

        $curl = curl_init();

        $options = array(
            CURLOPT_URL => $urlbase . "webservice/Users/Login",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 80,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "X-ENCRYPTED: 0",
                "x-api-key:" . $xapikey
            ],
            CURLOPT_USERPWD => "$username:$password",
            CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

        );

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        $result = json_decode($response, true);

        if (!General::isEmpty($result)) {
            foreach ($result['result'] as $k => $v) {
                if ($k == 'token') {
                    $token = $v;
                }
            }

            if ($err) {
                $response = "CURL Error #:" . $err;
            }
        }

        return $token;
    }

    public static function CasesRecordsLists()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');

        $x_row_order = env('X_ROW_ORDER');
        $x_row_limit = env('X_ROW_LIMIT');
        $x_row_order_field = env('X_ROW_ORDER_FIELD');

        $xtoken = PmcTestLogic::GenerarTokenSeguridad();
        $curl = curl_init();

        if ($xtoken != "") {

            $options = array(
                CURLOPT_URL => $urlbase . "webservice/Cases/RecordsList",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 100,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "x-api-key:" . $xapikey,
                    "x-token:" . $xtoken,
                    "x-row-order:" . $x_row_order,
                    "x-row-limit:" . $x_row_limit,
                    "x-row-order-field:" . $x_row_order_field
                ],
                CURLOPT_USERPWD => "$username:$password",
                CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

            );

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt_array($curl, $options);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            $result = json_decode($response, true);

            if (!General::isEmpty($result)) {

                $start_time_process = date("Y-m-d H:i:s");

                $newrecords = array();
                foreach ($result['result']['records'] as $k => $v) {
                    $newrecords[] = array(
                        'recordid'                        => $k
                    );
                }

                if ($err) {
                    $response = "CURL Error #:" . $err;
                }

                #Start Elimina los elementos duplicados y retorna solo los nuevos
                $arrayrecordscab = PmcTestModel::ToListRecordsCabAll();

                if (count($arrayrecordscab) > 0) {
                    foreach ($arrayrecordscab as $valor) {
                        foreach ($newrecords as $valor2) {
                            if ($valor->recordid == $valor2['recordid']) {
                                $borrar = array_search($valor2, $newrecords);
                                unset($newrecords[$borrar]);
                            }
                        }
                    }
                }
                #End Elimina los elementos duplicados y retorna solo los nuevos

                $data['recordscab'] = General::convertir_array_a_xml($newrecords);

                #Start Obteniene los valores para la tabla procesos log
                $number_register_pre_process =  PmcTestModel::RecordsCabCount();
                $number_register_post_process = $number_register_pre_process->recordid + count($newrecords);
                $number_register_new_process = count($newrecords);
                $process = "INSERT";
                $module = "Cases";
                $end_time_process = date("Y-m-d H:i:s");
                #End Obteniene los valores para la tabla procesos log

                $response = PmcTestModel::ToRegisterUpdateCab($data);

                $message = 'No se recibio respuesta de la Base Datos';
                $success = TRUE;

                if (!General::isEmpty($response)) {
                    $message = $response->message;
                    unset($response->message);
                    $success = ($response->success == 0 ? TRUE : FALSE);
                    unset($response->success);
                    if (!$success) {
                        $response = null;
                    }
                }

                #Start Registra los valores para la tabla procesos log
                PmcTestModel::ExecuteProcessDataAuditLog(
                    $number_register_pre_process->recordid,
                    $number_register_post_process,
                    $number_register_new_process,
                    $start_time_process,
                    $end_time_process,
                    $process,
                    $module
                );
                #End Registra los valores para la tabla procesos log

                return [$response, $message, $success];
            }
        } else {
            return [[], "Error al Generar Token PMC", "error"];
        }
    }

    public static function GetCaseWithRecordId()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');

        $xtoken = PmcTestLogic::GenerarTokenSeguridad();

        $message = 'No se recibio respuesta de la Base Datos';
        $response = '';
        $success = TRUE;

        if ($xtoken != "") {

            $response_records_cab = PmcTestModel::ToListRecordsCab();
            $recordid = 0;

            if (!General::isEmpty($response_records_cab)) {
                foreach ($response_records_cab as $records_cab) {

                    $recordid = $records_cab->recordid;

                    $curl = curl_init();
                    $options = array(
                        CURLOPT_URL => $urlbase . "webservice/Cases/Record/" . $recordid,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => [
                            "x-api-key:" . $xapikey,
                            "x-token:" . $xtoken
                        ],
                        CURLOPT_USERPWD => "$username:$password",
                        CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

                    );

                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

                    curl_setopt_array($curl, $options);

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    curl_close($curl);

                    if ($err) {
                        $response = "CURL Error #:" . $err;
                    }

                    $result = json_decode($response, true);

                    if (!General::isEmpty($result)) {

                        $newrecords = array();
                        $newrecords[] = array(
                            'case_id' =>  $result['result']['data']['case_id'],
                            'provider' =>  $result['result']['data']['provider'],
                            'insured' =>  $result['result']['data']['insured'],
                            'type_of_claim' =>  $result['result']['data']['type_of_claim'],
                            'case_number' =>  $result['result']['data']['case_number'],
                            'date_of_loss' =>  $result['result']['data']['date_of_loss'],
                            'date_of_service' =>  $result['result']['data']['date_of_service'],
                            'claim_number' =>  $result['result']['data']['claim_number'],
                            'first_notice_of_loss' =>  $result['result']['data']['first_notice_of_loss'],
                            'types_of_services' =>  $result['result']['data']['types_of_services'],
                            'total_bill_amount' =>  $result['result']['data']['total_bill_amount'],
                            'total_collections' =>  $result['result']['data']['total_collections'],
                            'total_balance' =>  $result['result']['data']['total_balance'],
                            'voluntary_payment_date' =>  $result['result']['data']['voluntary_payment_date'],
                            'policy_number' =>  $result['result']['data']['policy_number'],
                            'insurance_company' =>  $result['result']['data']['insurance_company'],
                            'day_demand_sent_10' =>  $result['result']['data']['10_day_demand_sent'],
                            'case_filed' =>  $result['result']['data']['case_filed'],
                            'corporate_representative' =>  $result['result']['data']['corporate_representative'],
                            'engineer' =>  $result['result']['data']['engineer'],
                            'insurance_expert' =>  $result['result']['data']['insurance_expert'],
                            'pricing_expert' =>  $result['result']['data']['pricing_expert'],
                            'indoor_environmental_professio' =>  $result['result']['data']['indoor_environmental_professio'],
                            'inspector' =>  $result['result']['data']['inspector'],
                            'ps_corporate_rep' =>  $result['result']['data']['ps_corporate_rep']
                        );

                        $data = array();
                        $data['recordid'] = $recordid;
                        $data['recordsdet'] = General::convertir_array_a_xml($newrecords);

                        $response = PmcTestModel::ToRegisterUpdateDet($data);

                        if (!General::isEmpty($response)) {
                            $message = $response->message;
                            unset($response->message);
                            $success = ($response->success == 0 ? TRUE : FALSE);
                            unset($response->success);
                            if (!$success) {
                                $response = null;
                            }
                        }
                    }
                }
                return [$response, $message, $success];
            } else {
                return [[], "Records Cab has no new records", "error"];
            }
        } else {
            return [[], "Generate Error Token PMC", "error"];
        }
    }

    public static function InsertAllCaseWithRecordId()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');

        $xtoken = PmcTestLogic::GenerarTokenSeguridad();

        $message = 'No se recibio respuesta de la Base Datos';
        $success = TRUE;

        if ($xtoken != "") {

            $response_records_cab = PmcTestModel::ToListRecordsCab();
            $recordid = 0;

            if (!General::isEmpty($response_records_cab)) {
                foreach ($response_records_cab as $records_cab) {
                    $recordid = $records_cab->recordid;

                    $curl = curl_init();
                    $options = array(
                        CURLOPT_URL => $urlbase . "webservice/Cases/Record/" . $recordid,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 100,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => [
                            "x-api-key:" . $xapikey,
                            "x-token:" . $xtoken
                        ],
                        CURLOPT_USERPWD => "$username:$password",
                        CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

                    );

                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

                    curl_setopt_array($curl, $options);

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    curl_close($curl);

                    if ($err) {
                        $response = "CURL Error #:" . $err;
                    }

                    $result = json_decode($response, true);

                    if (!General::isEmpty($result)) {

                        $newrecords = array();
                        $newrecords[] = array(
                            'case_id' =>  $result['result']['data']['case_id'],
                            'provider' =>  $result['result']['data']['provider'],
                            'insured' =>  $result['result']['data']['insured'],
                            'type_of_claim' =>  $result['result']['data']['type_of_claim'],
                            'case_number' =>  $result['result']['data']['case_number'],
                            'date_of_loss' =>  $result['result']['data']['date_of_loss'],
                            'date_of_service' =>  $result['result']['data']['date_of_service'],
                            'claim_number' =>  $result['result']['data']['claim_number'],
                            'first_notice_of_loss' =>  $result['result']['data']['first_notice_of_loss'],
                            'types_of_services' =>  $result['result']['data']['types_of_services'],
                            'total_bill_amount' =>  $result['result']['data']['total_bill_amount'],
                            'total_collections' =>  $result['result']['data']['total_collections'],
                            'total_balance' =>  $result['result']['data']['total_balance'],
                            'voluntary_payment_date' =>  $result['result']['data']['voluntary_payment_date'],
                            'policy_number' =>  $result['result']['data']['policy_number'],
                            'insurance_company' =>  $result['result']['data']['insurance_company'],
                            'day_demand_sent_10' =>  $result['result']['data']['10_day_demand_sent'],
                            'case_filed' =>  $result['result']['data']['case_filed'],
                            'corporate_representative' =>  $result['result']['data']['corporate_representative'],
                            'engineer' =>  $result['result']['data']['engineer'],
                            'insurance_expert' =>  $result['result']['data']['insurance_expert'],
                            'pricing_expert' =>  $result['result']['data']['pricing_expert'],
                            'indoor_environmental_professio' =>  $result['result']['data']['indoor_environmental_professio'],
                            'inspector' =>  $result['result']['data']['inspector'],
                            'ps_corporate_rep' =>  $result['result']['data']['ps_corporate_rep']
                        );

                        $data = array();
                        $data['recordid'] = $recordid;
                        $data['recordsdet'] = General::convertir_array_a_xml($newrecords);

                        $response = PmcTestModel::ToRegisterDet($data);

                        if (!General::isEmpty($response)) {
                            $message = $response->message;
                            unset($response->message);
                            $success = ($response->success == 0 ? TRUE : FALSE);
                            unset($response->success);
                            if (!$success) {
                                $response = null;
                            }
                        }
                    }
                }
                return [$response, $message, $success];
            } else {
                return [[], "Records Cab has no new records", "error"];
            }
        } else {
            return [[], "Error al Generar Token PMC", "error"];
        }
    }

    public static function UpdateAllCaseWithRecordId()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');

        $xtoken = PmcTestLogic::GenerarTokenSeguridad();

        $message = 'No se recibio respuesta de la Base Datos';
        $success = TRUE;

        if ($xtoken != "") {

            $response_records_cab = PmcTestLogic::CasesListToUpdate();
            $recordid = 0;

            foreach ($response_records_cab as $records_cab) {
                $recordid = $records_cab['recordid'];

                $curl = curl_init();
                $options = array(
                    CURLOPT_URL => $urlbase . "webservice/Cases/Record/" . $recordid,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => [
                        "x-api-key:" . $xapikey,
                        "x-token:" . $xtoken
                    ],
                    CURLOPT_USERPWD => "$username:$password",
                    CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

                );

                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

                curl_setopt_array($curl, $options);

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    $response = "CURL Error #:" . $err;
                }

                $result = json_decode($response, true);

                if (!General::isEmpty($result)) {

                    $newrecords = array();
                    $newrecords[] = array(
                        'case_id' =>  $result['result']['data']['case_id'],
                        'provider' =>  $result['result']['data']['provider'],
                        'insured' =>  $result['result']['data']['insured'],
                        'type_of_claim' =>  $result['result']['data']['type_of_claim'],
                        'case_number' =>  $result['result']['data']['case_number'],
                        'date_of_loss' =>  $result['result']['data']['date_of_loss'],
                        'date_of_service' =>  $result['result']['data']['date_of_service'],
                        'claim_number' =>  $result['result']['data']['claim_number'],
                        'first_notice_of_loss' =>  $result['result']['data']['first_notice_of_loss'],
                        'types_of_services' =>  $result['result']['data']['types_of_services'],
                        'total_bill_amount' =>  $result['result']['data']['total_bill_amount'],
                        'total_collections' =>  $result['result']['data']['total_collections'],
                        'total_balance' =>  $result['result']['data']['total_balance'],
                        'voluntary_payment_date' =>  $result['result']['data']['voluntary_payment_date'],
                        'policy_number' =>  $result['result']['data']['policy_number'],
                        'insurance_company' =>  $result['result']['data']['insurance_company'],
                        'day_demand_sent_10' =>  $result['result']['data']['10_day_demand_sent'],
                        'case_filed' =>  $result['result']['data']['case_filed'],
                        'corporate_representative' =>  $result['result']['data']['corporate_representative'],
                        'engineer' =>  $result['result']['data']['engineer'],
                        'insurance_expert' =>  $result['result']['data']['insurance_expert'],
                        'pricing_expert' =>  $result['result']['data']['pricing_expert'],
                        'indoor_environmental_professio' =>  $result['result']['data']['indoor_environmental_professio'],
                        'inspector' =>  $result['result']['data']['inspector'],
                        'ps_corporate_rep' =>  $result['result']['data']['ps_corporate_rep']
                    );

                    $data = array();
                    $data['recordid'] = $recordid;
                    $data['recordsdet'] = General::convertir_array_a_xml($newrecords);

                    $response = PmcTestModel::ToUpdateDet($data);

                    if (!General::isEmpty($response)) {
                        $message = $response->message;
                        unset($response->message);
                        $success = ($response->success == 0 ? TRUE : FALSE);
                        unset($response->success);
                        if (!$success) {
                            $response = null;
                        }
                    }
                }
            }
            return [$response, $message, $success];
        } else {
            return [[], "Error al Generar Token PMC", "error"];
        }
    }

    public static function ProbandoTabla()
    {
        $resultado = PmcTestModel::ToListRecordsCabAll();
        return $resultado;
    }

    public static function InsertRecordsCabClaims()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');
        $x_row_order = env('X_ROW_ORDER');
        $x_row_limit = env('X_ROW_LIMIT');
        $x_row_order_field = env('X_ROW_ORDER_FIELD');

        $xtoken = PmcTestLogic::GenerarTokenSeguridad();
        $curl = curl_init();

        if ($xtoken != "") {

            $options = array(
                CURLOPT_URL => $urlbase . "webservice/Claims/RecordsList",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 100,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "x-api-key:" . $xapikey,
                    "x-token:" . $xtoken,
                    "x-row-order:" . $x_row_order,
                    "x-row-limit:" . $x_row_limit,
                    "x-row-order-field:" . $x_row_order_field
                ],
                CURLOPT_USERPWD => "$username:$password",
                CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

            );

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt_array($curl, $options);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            $result = json_decode($response, true);

            if (!General::isEmpty($result)) {

                $start_time_process = date("Y-m-d H:i:s");

                $newrecords = array();
                foreach ($result['result']['records'] as $k => $v) {
                    $newrecords[] = array(
                        'recordid'                        => $k
                    );
                }

                if ($err) {
                    $response = "CURL Error #:" . $err;
                }

                $arrayrecordscab = PmcTestModel::ToListRecordsCabClaimsAll();

                if (count($arrayrecordscab) > 0) {
                    foreach ($arrayrecordscab as $valor) {
                        foreach ($newrecords as $valor2) {
                            if ($valor->recordid == $valor2['recordid']) {
                                $borrar = array_search($valor2, $newrecords);
                                unset($newrecords[$borrar]);
                            }
                        }
                    }
                }

                $data['recordscab'] = General::convertir_array_a_xml($newrecords);

                #Start Obteniene los valores para la tabla procesos log
                $number_register_pre_process =  PmcTestModel::RecordsCabClaimsCount();
                $number_register_post_process = $number_register_pre_process->recordid + count($newrecords);
                $number_register_new_process = count($newrecords);
                $process = "INSERT";
                $module = "Claims";
                $end_time_process = date("Y-m-d H:i:s");
                #End Obteniene los valores para la tabla procesos log

                $response = PmcTestModel::InsertRecordsCabClaims($data);

                $message = 'No se recibio respuesta de la Base Datos';
                $success = TRUE;

                if (!General::isEmpty($response)) {
                    $message = $response->message;
                    unset($response->message);
                    $success = ($response->success == 0 ? TRUE : FALSE);
                    unset($response->success);
                    if (!$success) {
                        $response = null;
                    }
                }

                #Start Registra los valores para la tabla procesos log
                PmcTestModel::ExecuteProcessDataAuditLog(
                    $number_register_pre_process->recordid,
                    $number_register_post_process,
                    $number_register_new_process,
                    $start_time_process,
                    $end_time_process,
                    $process,
                    $module
                );

                return [$response, $message, $success];
            }
        } else {
            return [[], "Error al Generar Token PMC", "error"];
        }
    }

    public static function InsertRecordsDetClaims()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');

        $xtoken = PmcTestLogic::GenerarTokenSeguridad();

        $message = 'No se recibio respuesta de la Base Datos';
        $response = '';
        $success = TRUE;

        if ($xtoken != "") {

            $response_records_cab = PmcTestModel::ToListRecordsCabClaims();
            $recordid = 0;

            if (!General::isEmpty($response_records_cab)) {
                foreach ($response_records_cab as $records_cab) {

                    $recordid = $records_cab->recordid;

                    $curl = curl_init();
                    $options = array(
                        CURLOPT_URL => $urlbase . "webservice/Claims/Record/" . $recordid,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => [
                            "x-api-key:" . $xapikey,
                            "x-token:" . $xtoken
                        ],
                        CURLOPT_USERPWD => "$username:$password",
                        CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

                    );

                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

                    curl_setopt_array($curl, $options);

                    $response_api_pmc = curl_exec($curl);
                    $err = curl_error($curl);

                    curl_close($curl);

                    if ($err) {
                        $response_api_pmc = "CURL Error #:" . $err;
                    }

                    $result = json_decode($response_api_pmc, true);

                    if (!General::isEmpty($result)) {

                        $newrecords = array();
                        $newrecords[] = array(
                            'claim_id' =>  $result['result']['data']['claim_id'],
                            'claim_number' =>  $result['result']['data']['claim_number'],
                            'provider' =>  $result['result']['data']['provider'],
                            'portfolio' =>  $result['result']['data']['portfolio'],
                            'portfolio_purchase' =>  $result['result']['data']['portfolio_purchase'],
                            'type_of_claim' =>  $result['result']['data']['type_of_claim'],
                            'onboarding_status' =>  $result['result']['data']['onboarding_status'],
                            'claim_status' =>  $result['result']['data']['claim_status'],
                            'assigned_user_id' =>  $result['result']['data']['assigned_user_id'],
                            'similar_claims' =>  $result['result']['data']['similar_claims'],
                            'conducted_by' =>  $result['result']['data']['conducted_by'],
                            'case' =>  $result['result']['data']['case'],
                            'outside_case' =>  $result['result']['data']['outside_case'],
                            'county' =>  $result['result']['data']['county'],
                            'basic_litigation_status' =>  $result['result']['data']['basic_litigation_status'],
                            'portfolio_purchase_program' =>  $result['result']['data']['portfolio_purchase_program'],
                            'missing_documents_date' =>  $result['result']['data']['missing_documents_date'],
                            'type_of_property' =>  $result['result']['data']['type_of_property'],
                            'insured' =>  $result['result']['data']['insured'],
                            'insureds_state' =>  $result['result']['data']['insureds_state'],
                            'insureds_email' =>  $result['result']['data']['insureds_email'],
                            'insureds_address' =>  $result['result']['data']['insureds_address'],
                            'insurance_company' =>  $result['result']['data']['insurance_company'],
                            'policy_number' =>  $result['result']['data']['policy_number'],
                            'insurance_policy_details' =>  $result['result']['data']['insurance_policy_details'],
                            'insurance_company_email' =>  $result['result']['data']['insurance_company_email'],
                            'policy_valid_from' =>  $result['result']['data']['policy_valid_from'],
                            'insurance_policy_uploaded' =>  $result['result']['data']['insurance_policy_uploaded'],
                            'insurance_shared' =>  $result['result']['data']['insurance_shared'],
                            'policy_valid_to' =>  $result['result']['data']['policy_valid_to'],
                            'general_policy_limit' =>  $result['result']['data']['general_policy_limit'],
                            'insurance_shared_notes' =>  $result['result']['data']['insurance_shared_notes'],
                            'other_structures_policy_limit' =>  $result['result']['data']['other_structures_policy_limit'],
                            'dwelling_policy_limit' =>  $result['result']['data']['dwelling_policy_limit'],
                            'loss_of_use_policy_limit' =>  $result['result']['data']['loss_of_use_policy_limit'],
                            'personal_property_policy_limit' =>  $result['result']['data']['personal_property_policy_limit'],
                            'date_of_loss' =>  $result['result']['data']['date_of_loss'],
                            'date_of_service' =>  $result['result']['data']['date_of_service'],
                            'type_of_job' =>  $result['result']['data']['type_of_job'],
                            'types_of_services' =>  $result['result']['data']['types_of_services'],
                            'cause_of_loss' =>  $result['result']['data']['cause_of_loss'],
                            'pre_attorney_name' =>  $result['result']['data']['pre_attorney_name'],
                            'aob_dtp_attorney' =>  $result['result']['data']['aob_dtp_attorney'],
                            'pre_litigation_status' =>  $result['result']['data']['pre_litigation_status'],
                            'pre_court_case_number' =>  $result['result']['data']['pre_court_case_number'],
                            'attorney_contract_on_file' =>  $result['result']['data']['attorney_contract_on_file'],
                            'pre_county' =>  $result['result']['data']['pre_county'],
                            'ho_law_firm' =>  $result['result']['data']['ho_law_firm'],
                            'ho_attorney' =>  $result['result']['data']['ho_attorney'],
                            'ho_attorney_conf_request_date' =>  $result['result']['data']['ho_attorney_conf_request_date'],
                            'ho_attorney_confirmation_statu' =>  $result['result']['data']['ho_attorney_confirmation_statu'],
                            'aob_dtp_law_firm' =>  $result['result']['data']['aob_dtp_law_firm'],
                            'mechanic_lien_conf_status' =>  $result['result']['data']['mechanic_lien_conf_status'],
                            'requires_attention' =>  $result['result']['data']['requires_attention'],
                            'onb_comments' =>  $result['result']['data']['onb_comments'],
                            'claim_underwriter' =>  $result['result']['data']['claim_underwriter'],
                            'comments_for_client' =>  $result['result']['data']['comments_for_client'],
                            'onb_street' =>  $result['result']['data']['onb_street'],
                            'claim_acceptant' =>  $result['result']['data']['claim_acceptant'],
                            'onb_zip' =>  $result['result']['data']['onb_zip'],
                            'onb_city' =>  $result['result']['data']['onb_city'],
                            'onb_claim_number' =>  $result['result']['data']['onb_claim_number'],
                            'state' =>  $result['result']['data']['state'],
                            'onb_email' =>  $result['result']['data']['onb_email'],
                            'onb_policy_number' =>  $result['result']['data']['onb_policy_number'],
                            'home_owner_signature' =>  $result['result']['data']['home_owner_signature'],
                            'client_signature' =>  $result['result']['data']['client_signature'],
                            'aob_date' =>  $result['result']['data']['aob_date'],
                            'onb_date_of_loss' =>  $result['result']['data']['onb_date_of_loss'],
                            'days_apart_dofn_aob' =>  $result['result']['data']['days_apart_dofn_aob'],
                            'date_of_first_notification' =>  $result['result']['data']['date_of_first_notification'],
                            'per_unit_cost_estimate' =>  $result['result']['data']['per_unit_cost_estimate'],
                            'dates_verified' =>  $result['result']['data']['dates_verified'],
                            'windspeed' =>  $result['result']['data']['windspeed'],
                            'roof_area' =>  $result['result']['data']['roof_area'],
                            'wind_gust' =>  $result['result']['data']['wind_gust'],
                            'dry_logs' =>  $result['result']['data']['dry_logs'],
                            'report_with_pics' =>  $result['result']['data']['report_with_pics'],
                            'denial_reason' =>  $result['result']['data']['denial_reason'],
                            'need_labs' =>  $result['result']['data']['need_labs'],
                            'onb_others' =>  $result['result']['data']['onb_others'],
                            'lop_date' =>  $result['result']['data']['lop_date'],
                            'lop_on_file' =>  $result['result']['data']['lop_on_file'],
                            'lop_signed_by_ho' =>  $result['result']['data']['lop_signed_by_ho'],
                            'lop_signed_by_client' =>  $result['result']['data']['lop_signed_by_client'],
                            'onb_warning_to_acceptant' =>  $result['result']['data']['onb_warning_to_acceptant'],
                            'onb_warnings' =>  $result['result']['data']['onb_warnings'],
                            'date_of_completion' =>  $result['result']['data']['date_of_completion'],
                            'total_bill_amount' =>  $result['result']['data']['total_bill_amount'],
                            'prior_collections' =>  $result['result']['data']['prior_collections'],
                            'overhead_and_profit' =>  $result['result']['data']['overhead_and_profit'],
                            'adjustments' =>  $result['result']['data']['adjustments'],
                            'adjusted_face_value' =>  $result['result']['data']['adjusted_face_value'],
                            'purchase_price' =>  $result['result']['data']['purchase_price'],
                            'perc_advance' =>  $result['result']['data']['perc_advance'],
                            'cash_to_seller' =>  $result['result']['data']['cash_to_seller'],
                            'factor_fee' =>  $result['result']['data']['factor_fee'],
                            'write_off' =>  $result['result']['data']['write_off'],
                            'hurdle' =>  $result['result']['data']['hurdle'],
                            'hurdle_percent' =>  $result['result']['data']['hurdle_percent'],
                            'total_voluntary_ollections' =>  $result['result']['data']['total_voluntary_ollections'],
                            'total_pre_suit_collections' =>  $result['result']['data']['total_pre_suit_collections'],
                            'total_litigated_collections' =>  $result['result']['data']['total_litigated_collections'],
                            'total_collections' =>  $result['result']['data']['total_collections'],
                            'total_pmc_collections' =>  $result['result']['data']['total_pmc_collections'],
                            'total_balance_owed' =>  $result['result']['data']['total_balance_owed'],
                            'remaining_to_hurdle' =>  $result['result']['data']['remaining_to_hurdle'],
                            'total_profit' =>  $result['result']['data']['total_profit'],
                            'refundable_reserve' =>  $result['result']['data']['refundable_reserve'],
                            'limit_reserve' =>  $result['result']['data']['limit_reserve'],
                            'internal_cash_transfer' =>  $result['result']['data']['internal_cash_transfer'],
                            'total_penalty' =>  $result['result']['data']['total_penalty'],
                            'createdtime' =>  $result['result']['data']['createdtime'],
                            'underwriting_started_time' =>  $result['result']['data']['underwriting_started_time'],
                            'underwritten_time' =>  $result['result']['data']['underwritten_time'],
                            'approved_rejected_time' =>  $result['result']['data']['approved_rejected_time'],
                            'purchased_time' =>  $result['result']['data']['purchased_time'],
                            'buyback_date' =>  $result['result']['data']['buyback_date'],
                            'litigation_started_date' =>  $result['result']['data']['litigation_started_date'],
                            'vol_coll_started_date' =>  $result['result']['data']['vol_coll_started_date'],
                            'litigation_finished_date' =>  $result['result']['data']['litigation_finished_date'],
                            'claim_paid_date' =>  $result['result']['data']['claim_paid_date'],
                            'claim_closed_date' =>  $result['result']['data']['claim_closed_date'],
                            'created_date' =>  $result['result']['data']['created_date'],
                            'total_reserved_released' =>  $result['result']['data']['total_reserved_released'],
                            'last_reserves_released_date' =>  $result['result']['data']['last_reserves_released_date'],
                            'total_reserves_to_be_released' =>  $result['result']['data']['total_reserves_to_be_released'],
                            'buyback_status' =>  $result['result']['data']['buyback_status'],
                            'buyback_reason' =>  $result['result']['data']['buyback_reason'],
                            'buyback_penalty_percent' =>  $result['result']['data']['buyback_penalty_percent'],
                            'buyback_amount' =>  $result['result']['data']['buyback_amount'],
                            'buyback_portfolio_purchase' =>  $result['result']['data']['buyback_portfolio_purchase'],
                            'note' =>  $result['result']['data']['note'],
                            'lock_automation' =>  $result['result']['data']['lock_automation'],
                            'dummy_field1' =>  $result['result']['data']['dummy_field1'],
                            'number' =>  $result['result']['data']['number'],
                            'shownerid' =>  $result['result']['data']['shownerid'],
                            'created_user_id' =>  $result['result']['data']['created_user_id'],
                            'modifiedtime' =>  $result['result']['data']['modifiedtime'],
                            'modifiedby' =>  $result['result']['data']['modifiedby'],
                            'internal_temporal_data' =>  $result['result']['data']['internal_temporal_data'],
                            'public_adjuster' => $result['result']['data']['public_adjuster'],
                            'public_adjuster_confirm_status' => $result['result']['data']['public_adjuster_confirm_status']
                        );

                        $data = array();
                        $data['recordid'] = $recordid;
                        $data['recordsdet'] = General::convertir_array_a_xml($newrecords);

                        $response = PmcTestModel::InsertRecordsDetClaims($data);

                        if (!General::isEmpty($response)) {
                            $message = $response->message;
                            unset($response->message);
                            $success = ($response->success == 0 ? FALSE : TRUE);
                            unset($response->success);
                            if (!$success) {
                                $response = null;
                                return [$response, $message, $success];
                            }
                        }
                    }
                }
                return [$response, $message, $success];
            } else {
                return [[], "Records Cab has no new records", "error"];
            }
        } else {
            return [[], "Generate Error Token PMC", "error"];
        }
    }

    public static function UpdateRecordsDetClaims()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');

        $xtoken = PmcTestLogic::GenerarTokenSeguridad();

        $message = 'No se recibio respuesta de la Base Datos';
        $response = '';
        $success = TRUE;

        if ($xtoken != "") {

            $response_records_cab = PmcTestLogic::ClaimsListToUpdate();
            $recordid = 0;

            if (!General::isEmpty($response_records_cab)) {
                foreach ($response_records_cab as $records_cab) {

                    $recordid = $records_cab['recordid'];

                    $curl = curl_init();
                    $options = array(
                        CURLOPT_URL => $urlbase . "webservice/Claims/Record/" . $recordid,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => [
                            "x-api-key:" . $xapikey,
                            "x-token:" . $xtoken
                        ],
                        CURLOPT_USERPWD => "$username:$password",
                        CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

                    );

                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

                    curl_setopt_array($curl, $options);

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    curl_close($curl);

                    if ($err) {
                        $response = "CURL Error #:" . $err;
                    }

                    $result = json_decode($response, true);

                    if (!General::isEmpty($result)) {

                        $newrecords = array();
                        $newrecords[] = array(
                            'claim_id' =>  $result['result']['data']['claim_id'],
                            'claim_number' =>  $result['result']['data']['claim_number'],
                            'provider' =>  $result['result']['data']['provider'],
                            'portfolio' =>  $result['result']['data']['portfolio'],
                            'portfolio_purchase' =>  $result['result']['data']['portfolio_purchase'],
                            'type_of_claim' =>  $result['result']['data']['type_of_claim'],
                            'onboarding_status' =>  $result['result']['data']['onboarding_status'],
                            'claim_status' =>  $result['result']['data']['claim_status'],
                            'assigned_user_id' =>  $result['result']['data']['assigned_user_id'],
                            'similar_claims' =>  $result['result']['data']['similar_claims'],
                            'conducted_by' =>  $result['result']['data']['conducted_by'],
                            'case' =>  $result['result']['data']['case'],
                            'outside_case' =>  $result['result']['data']['outside_case'],
                            'county' =>  $result['result']['data']['county'],
                            'basic_litigation_status' =>  $result['result']['data']['basic_litigation_status'],
                            'portfolio_purchase_program' =>  $result['result']['data']['portfolio_purchase_program'],
                            'missing_documents_date' =>  $result['result']['data']['missing_documents_date'],
                            'type_of_property' =>  $result['result']['data']['type_of_property'],
                            'insured' =>  $result['result']['data']['insured'],
                            'insureds_state' =>  $result['result']['data']['insureds_state'],
                            'insureds_email' =>  $result['result']['data']['insureds_email'],
                            'insureds_address' =>  $result['result']['data']['insureds_address'],
                            'insurance_company' =>  $result['result']['data']['insurance_company'],
                            'policy_number' =>  $result['result']['data']['policy_number'],
                            'insurance_policy_details' =>  $result['result']['data']['insurance_policy_details'],
                            'insurance_company_email' =>  $result['result']['data']['insurance_company_email'],
                            'policy_valid_from' =>  $result['result']['data']['policy_valid_from'],
                            'insurance_policy_uploaded' =>  $result['result']['data']['insurance_policy_uploaded'],
                            'insurance_shared' =>  $result['result']['data']['insurance_shared'],
                            'policy_valid_to' =>  $result['result']['data']['policy_valid_to'],
                            'general_policy_limit' =>  $result['result']['data']['general_policy_limit'],
                            'insurance_shared_notes' =>  $result['result']['data']['insurance_shared_notes'],
                            'other_structures_policy_limit' =>  $result['result']['data']['other_structures_policy_limit'],
                            'dwelling_policy_limit' =>  $result['result']['data']['dwelling_policy_limit'],
                            'loss_of_use_policy_limit' =>  $result['result']['data']['loss_of_use_policy_limit'],
                            'personal_property_policy_limit' =>  $result['result']['data']['personal_property_policy_limit'],
                            'date_of_loss' =>  $result['result']['data']['date_of_loss'],
                            'date_of_service' =>  $result['result']['data']['date_of_service'],
                            'type_of_job' =>  $result['result']['data']['type_of_job'],
                            'types_of_services' =>  $result['result']['data']['types_of_services'],
                            'cause_of_loss' =>  $result['result']['data']['cause_of_loss'],
                            'pre_attorney_name' =>  $result['result']['data']['pre_attorney_name'],
                            'aob_dtp_attorney' =>  $result['result']['data']['aob_dtp_attorney'],
                            'pre_litigation_status' =>  $result['result']['data']['pre_litigation_status'],
                            'pre_court_case_number' =>  $result['result']['data']['pre_court_case_number'],
                            'attorney_contract_on_file' =>  $result['result']['data']['attorney_contract_on_file'],
                            'pre_county' =>  $result['result']['data']['pre_county'],
                            'ho_law_firm' =>  $result['result']['data']['ho_law_firm'],
                            'ho_attorney' =>  $result['result']['data']['ho_attorney'],
                            'ho_attorney_conf_request_date' =>  $result['result']['data']['ho_attorney_conf_request_date'],
                            'ho_attorney_confirmation_statu' =>  $result['result']['data']['ho_attorney_confirmation_statu'],
                            'aob_dtp_law_firm' =>  $result['result']['data']['aob_dtp_law_firm'],
                            'mechanic_lien_conf_status' =>  $result['result']['data']['mechanic_lien_conf_status'],
                            'requires_attention' =>  $result['result']['data']['requires_attention'],
                            'onb_comments' =>  $result['result']['data']['onb_comments'],
                            'claim_underwriter' =>  $result['result']['data']['claim_underwriter'],
                            'comments_for_client' =>  $result['result']['data']['comments_for_client'],
                            'onb_street' =>  $result['result']['data']['onb_street'],
                            'claim_acceptant' =>  $result['result']['data']['claim_acceptant'],
                            'onb_zip' =>  $result['result']['data']['onb_zip'],
                            'onb_city' =>  $result['result']['data']['onb_city'],
                            'onb_claim_number' =>  $result['result']['data']['onb_claim_number'],
                            'state' =>  $result['result']['data']['state'],
                            'onb_email' =>  $result['result']['data']['onb_email'],
                            'onb_policy_number' =>  $result['result']['data']['onb_policy_number'],
                            'home_owner_signature' =>  $result['result']['data']['home_owner_signature'],
                            'client_signature' =>  $result['result']['data']['client_signature'],
                            'aob_date' =>  $result['result']['data']['aob_date'],
                            'onb_date_of_loss' =>  $result['result']['data']['onb_date_of_loss'],
                            'days_apart_dofn_aob' =>  $result['result']['data']['days_apart_dofn_aob'],
                            'date_of_first_notification' =>  $result['result']['data']['date_of_first_notification'],
                            'per_unit_cost_estimate' =>  $result['result']['data']['per_unit_cost_estimate'],
                            'dates_verified' =>  $result['result']['data']['dates_verified'],
                            'windspeed' =>  $result['result']['data']['windspeed'],
                            'roof_area' =>  $result['result']['data']['roof_area'],
                            'wind_gust' =>  $result['result']['data']['wind_gust'],
                            'dry_logs' =>  $result['result']['data']['dry_logs'],
                            'report_with_pics' =>  $result['result']['data']['report_with_pics'],
                            'denial_reason' =>  $result['result']['data']['denial_reason'],
                            'need_labs' =>  $result['result']['data']['need_labs'],
                            'onb_others' =>  $result['result']['data']['onb_others'],
                            'lop_date' =>  $result['result']['data']['lop_date'],
                            'lop_on_file' =>  $result['result']['data']['lop_on_file'],
                            'lop_signed_by_ho' =>  $result['result']['data']['lop_signed_by_ho'],
                            'lop_signed_by_client' =>  $result['result']['data']['lop_signed_by_client'],
                            'onb_warning_to_acceptant' =>  $result['result']['data']['onb_warning_to_acceptant'],
                            'onb_warnings' =>  $result['result']['data']['onb_warnings'],
                            'date_of_completion' =>  $result['result']['data']['date_of_completion'],
                            'total_bill_amount' =>  $result['result']['data']['total_bill_amount'],
                            'prior_collections' =>  $result['result']['data']['prior_collections'],
                            'overhead_and_profit' =>  $result['result']['data']['overhead_and_profit'],
                            'adjustments' =>  $result['result']['data']['adjustments'],
                            'adjusted_face_value' =>  $result['result']['data']['adjusted_face_value'],
                            'purchase_price' =>  $result['result']['data']['purchase_price'],
                            'perc_advance' =>  $result['result']['data']['perc_advance'],
                            'cash_to_seller' =>  $result['result']['data']['cash_to_seller'],
                            'factor_fee' =>  $result['result']['data']['factor_fee'],
                            'write_off' =>  $result['result']['data']['write_off'],
                            'hurdle' =>  $result['result']['data']['hurdle'],
                            'hurdle_percent' =>  $result['result']['data']['hurdle_percent'],
                            'total_voluntary_ollections' =>  $result['result']['data']['total_voluntary_ollections'],
                            'total_pre_suit_collections' =>  $result['result']['data']['total_pre_suit_collections'],
                            'total_litigated_collections' =>  $result['result']['data']['total_litigated_collections'],
                            'total_collections' =>  $result['result']['data']['total_collections'],
                            'total_pmc_collections' =>  $result['result']['data']['total_pmc_collections'],
                            'total_balance_owed' =>  $result['result']['data']['total_balance_owed'],
                            'remaining_to_hurdle' =>  $result['result']['data']['remaining_to_hurdle'],
                            'total_profit' =>  $result['result']['data']['total_profit'],
                            'refundable_reserve' =>  $result['result']['data']['refundable_reserve'],
                            'limit_reserve' =>  $result['result']['data']['limit_reserve'],
                            'internal_cash_transfer' =>  $result['result']['data']['internal_cash_transfer'],
                            'total_penalty' =>  $result['result']['data']['total_penalty'],
                            'createdtime' =>  $result['result']['data']['createdtime'],
                            'underwriting_started_time' =>  $result['result']['data']['underwriting_started_time'],
                            'underwritten_time' =>  $result['result']['data']['underwritten_time'],
                            'approved_rejected_time' =>  $result['result']['data']['approved_rejected_time'],
                            'purchased_time' =>  $result['result']['data']['purchased_time'],
                            'buyback_date' =>  $result['result']['data']['buyback_date'],
                            'litigation_started_date' =>  $result['result']['data']['litigation_started_date'],
                            'vol_coll_started_date' =>  $result['result']['data']['vol_coll_started_date'],
                            'litigation_finished_date' =>  $result['result']['data']['litigation_finished_date'],
                            'claim_paid_date' =>  $result['result']['data']['claim_paid_date'],
                            'claim_closed_date' =>  $result['result']['data']['claim_closed_date'],
                            'created_date' =>  $result['result']['data']['created_date'],
                            'total_reserved_released' =>  $result['result']['data']['total_reserved_released'],
                            'last_reserves_released_date' =>  $result['result']['data']['last_reserves_released_date'],
                            'total_reserves_to_be_released' =>  $result['result']['data']['total_reserves_to_be_released'],
                            'buyback_status' =>  $result['result']['data']['buyback_status'],
                            'buyback_reason' =>  $result['result']['data']['buyback_reason'],
                            'buyback_penalty_percent' =>  $result['result']['data']['buyback_penalty_percent'],
                            'buyback_amount' =>  $result['result']['data']['buyback_amount'],
                            'buyback_portfolio_purchase' =>  $result['result']['data']['buyback_portfolio_purchase'],
                            'note' =>  $result['result']['data']['note'],
                            'lock_automation' =>  $result['result']['data']['lock_automation'],
                            'dummy_field1' =>  $result['result']['data']['dummy_field1'],
                            'number' =>  $result['result']['data']['number'],
                            'shownerid' =>  $result['result']['data']['shownerid'],
                            'created_user_id' =>  $result['result']['data']['created_user_id'],
                            'modifiedtime' =>  $result['result']['data']['modifiedtime'],
                            'modifiedby' =>  $result['result']['data']['modifiedby'],
                            'internal_temporal_data' =>  $result['result']['data']['internal_temporal_data'],
                            'public_adjuster' => $result['result']['data']['public_adjuster'],
                            'public_adjuster_confirm_status' => $result['result']['data']['public_adjuster_confirm_status']
                        );

                        $data = array();
                        $data['recordid'] = $recordid;
                        $data['recordsdet'] = General::convertir_array_a_xml($newrecords);
                        $response = PmcTestModel::UpdateRecordsDetClaims($data);

                        if (!General::isEmpty($response)) {
                            $message = $response->message;
                            unset($response->message);
                            $success = ($response->success == 0 ? FALSE : TRUE);
                            unset($response->success);
                            if (!$success) {
                                $response = null;
                                return [$response, $message, $success];
                            }
                        }
                    }
                }
                return [$response, $message, $success];
            } else {
                return [[], "Records Cab has no new records", "error"];
            }
        } else {
            return [[], "Generate Error Token PMC", "error"];
        }
    }

    public static function InsertRecordsCabClaimsCollections()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');
        $x_row_order = env('X_ROW_ORDER');
        $x_row_limit = env('X_ROW_LIMIT');
        $x_row_order_field = env('X_ROW_ORDER_FIELD');

        $xtoken = PmcTestLogic::GenerarTokenSeguridad();
        $curl = curl_init();

        if ($xtoken != "") {

            $options = array(
                CURLOPT_URL => $urlbase . "webservice/ClaimCollections/RecordsList",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 100,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "x-api-key:" . $xapikey,
                    "x-token:" . $xtoken,
                    "x-row-order:" . $x_row_order,
                    "x-row-limit:" . $x_row_limit,
                    "x-row-order-field:" . $x_row_order_field
                ],
                CURLOPT_USERPWD => "$username:$password",
                CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

            );

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt_array($curl, $options);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            $result = json_decode($response, true);

            if (!General::isEmpty($result)) {

                $start_time_process = date("Y-m-d H:i:s");

                $newrecords = array();
                foreach ($result['result']['records'] as $k => $v) {
                    $newrecords[] = array(
                        'recordid'                        => $k
                    );
                }

                if ($err) {
                    $response = "CURL Error #:" . $err;
                }

                $arrayrecordscab = PmcTestModel::ToListRecordsCabClaimsCollectionsAll();

                if (count($arrayrecordscab) > 0) {
                    foreach ($arrayrecordscab as $valor) {
                        foreach ($newrecords as $valor2) {
                            if ($valor->recordid == $valor2['recordid']) {
                                $borrar = array_search($valor2, $newrecords);
                                unset($newrecords[$borrar]);
                            }
                        }
                    }
                }

                $data['recordscab'] = General::convertir_array_a_xml($newrecords);

                #Start Obteniene los valores para la tabla procesos log
                $number_register_pre_process =  PmcTestModel::RecordsCabClaimsCollectionCount();
                $number_register_post_process = $number_register_pre_process->recordid + count($newrecords);
                $number_register_new_process = count($newrecords);
                $process = "INSERT";
                $module = "ClaimsCollections";
                $end_time_process = date("Y-m-d H:i:s");
                #End Obteniene los valores para la tabla procesos log

                $response = PmcTestModel::InsertRecordsCabClaimsCollections($data);

                $message = 'No se recibio respuesta de la Base Datos';
                $success = TRUE;

                if (!General::isEmpty($response)) {
                    $message = $response->message;
                    unset($response->message);
                    $success = ($response->success == 0 ? TRUE : FALSE);
                    unset($response->success);
                    if (!$success) {
                        $response = null;
                    }
                }

                #Start Registra los valores para la tabla procesos log
                PmcTestModel::ExecuteProcessDataAuditLog(
                    $number_register_pre_process->recordid,
                    $number_register_post_process,
                    $number_register_new_process,
                    $start_time_process,
                    $end_time_process,
                    $process,
                    $module
                );
                #End Registra los valores para la tabla procesos log

                return [$response, $message, $success];
            }
        } else {
            return [[], "Error al Generar Token PMC", "error"];
        }
    }

    public static function InsertRecordsDetClaimsCollections()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');

        $xtoken = PmcTestLogic::GenerarTokenSeguridad();

        $message = 'No se recibio respuesta de la Base Datos';
        $response = '';
        $success = TRUE;

        if ($xtoken != "") {

            $response_records_cab = PmcTestModel::ToListRecordsCabClaimsCollections();
            $recordid = 0;

            if (!General::isEmpty($response_records_cab)) {
                foreach ($response_records_cab as $records_cab) {

                    $recordid = $records_cab->recordid;

                    $curl = curl_init();
                    $options = array(
                        CURLOPT_URL => $urlbase . "webservice/ClaimCollections/Record/" . $recordid,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => [
                            "x-api-key:" . $xapikey,
                            "x-token:" . $xtoken
                        ],
                        CURLOPT_USERPWD => "$username:$password",
                        CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

                    );

                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

                    curl_setopt_array($curl, $options);

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    curl_close($curl);

                    if ($err) {
                        $response = "CURL Error #:" . $err;
                    }

                    $result = json_decode($response, true);

                    if (!General::isEmpty($result)) {

                        $newrecords = array();
                        $newrecords[] = array(
                            'claim_collection_name' =>  $result['result']['data']['claim_collection_name'],
                            'assigned_value' =>  $result['result']['data']['assigned_value'],
                            'assigned_below_hurdle' =>  $result['result']['data']['assigned_below_hurdle'],
                            'assigned_refundable_reserve' =>  $result['result']['data']['assigned_refundable_reserve'],
                            'assigned_limit_reserve' =>  $result['result']['data']['assigned_limit_reserve'],
                            'disbursed_date' =>  $result['result']['data']['disbursed_date'],
                            'portfolio' =>  $result['result']['data']['portfolio'],
                            'portfolio_purchase' =>  $result['result']['data']['portfolio_purchase'],
                            'claim' =>  $result['result']['data']['claim'],
                            'collection' =>  $result['result']['data']['collection'],
                            'number' =>  $result['result']['data']['number'],
                            'assigned_user_id' =>  $result['result']['data']['assigned_user_id'],
                            'createdtime' =>  $result['result']['data']['createdtime'],
                            'created_user_id' =>  $result['result']['data']['created_user_id'],
                            'modifiedtime' =>  $result['result']['data']['modifiedtime'],
                            'modifiedby' =>  $result['result']['data']['modifiedby'],
                            'shownerid' =>  $result['result']['data']['shownerid']
                        );

                        $data = array();
                        $data['recordid'] = $recordid;
                        $data['recordsdet'] = General::convertir_array_a_xml($newrecords);

                        $response = PmcTestModel::InsertRecordsDetClaimsCollections($data);

                        if (!General::isEmpty($response)) {
                            $message = $response->message;
                            unset($response->message);
                            $success = ($response->success == 0 ? TRUE : FALSE);
                            unset($response->success);
                            if (!$success) {
                                $response = null;
                            }
                        }
                    }
                }
                return [$response, $message, $success];
            } else {
                return [[], "Records Cab has no new records", "error"];
            }
        } else {
            return [[], "Generate Error Token PMC", "error"];
        }
    }

    public static function UpdateRecordsDetClaimsCollections()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');

        $xtoken = PmcTestLogic::GenerarTokenSeguridad();

        $message = 'No se recibio respuesta de la Base Datos';
        $response = '';
        $success = TRUE;

        if ($xtoken != "") {

            $response_records_cab = PmcTestLogic::ClaimsCollectionsListToUpdate();
            $recordid = 0;

            if (!General::isEmpty($response_records_cab)) {
                foreach ($response_records_cab as $records_cab) {

                    $recordid = $records_cab['recordid'];

                    $curl = curl_init();
                    $options = array(
                        CURLOPT_URL => $urlbase . "webservice/ClaimCollections/Record/" . $recordid,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => [
                            "x-api-key:" . $xapikey,
                            "x-token:" . $xtoken
                        ],
                        CURLOPT_USERPWD => "$username:$password",
                        CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

                    );

                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

                    curl_setopt_array($curl, $options);

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    curl_close($curl);

                    if ($err) {
                        $response = "CURL Error #:" . $err;
                    }

                    $result = json_decode($response, true);

                    if (!General::isEmpty($result)) {

                        $newrecords = array();
                        $newrecords[] = array(
                            'claim_collection_name' =>  $result['result']['data']['claim_collection_name'],
                            'assigned_value' =>  $result['result']['data']['assigned_value'],
                            'assigned_below_hurdle' =>  $result['result']['data']['assigned_below_hurdle'],
                            'assigned_refundable_reserve' =>  $result['result']['data']['assigned_refundable_reserve'],
                            'assigned_limit_reserve' =>  $result['result']['data']['assigned_limit_reserve'],
                            'disbursed_date' =>  $result['result']['data']['disbursed_date'],
                            'portfolio' =>  $result['result']['data']['portfolio'],
                            'portfolio_purchase' =>  $result['result']['data']['portfolio_purchase'],
                            'claim' =>  $result['result']['data']['claim'],
                            'collection' =>  $result['result']['data']['collection'],
                            'number' =>  $result['result']['data']['number'],
                            'assigned_user_id' =>  $result['result']['data']['assigned_user_id'],
                            'createdtime' =>  $result['result']['data']['createdtime'],
                            'created_user_id' =>  $result['result']['data']['created_user_id'],
                            'modifiedtime' =>  $result['result']['data']['modifiedtime'],
                            'modifiedby' =>  $result['result']['data']['modifiedby'],
                            'shownerid' =>  $result['result']['data']['shownerid']
                        );

                        $data = array();
                        $data['recordid'] = $recordid;
                        $data['recordsdet'] = General::convertir_array_a_xml($newrecords);

                        $response = PmcTestModel::UpdateRecordsDetClaimsCollections($data);

                        if (!General::isEmpty($response)) {
                            $message = $response->message;
                            unset($response->message);
                            $success = ($response->success == 0 ? TRUE : FALSE);
                            unset($response->success);
                            if (!$success) {
                                $response = null;
                            }
                        }
                    }
                }
                return [$response, $message, $success];
            } else {
                return [[], "Records Cab has no new records", "error"];
            }
        } else {
            return [[], "Generate Error Token PMC", "error"];
        }
    }

    public static function ClaimsListToUpdate()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');

        $xtoken = PmcTestLogic::GenerarTokenSeguridad();
        $curl = curl_init();

        if ($xtoken != "") {

            $options = array(
                CURLOPT_URL => $urlbase . "webservice/Claims/RecordsList",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 100,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "x-api-key:" . $xapikey,
                    "x-token:" . $xtoken,
                    "x-row-order:" . "DESC",
                    "x-row-limit:" . "500",
                    "x-row-order-field:" . "modifiedtime"
                ],
                CURLOPT_USERPWD => "$username:$password",
                CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

            );

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt_array($curl, $options);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            $result = json_decode($response, true);

            if (!General::isEmpty($result)) {

                $newrecords = array();
                foreach ($result['result']['records'] as $k => $v) {
                    $newrecords[] = array(
                        'recordid'                        => $k
                    );
                }

                return $newrecords;
            }

            if ($err) {
                $response = "CURL Error #:" . $err;
            }
        } else {
            return [[], "Error al Generar Token PMC", "error"];
        }
    }

    public static function CasesListToUpdate()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');

        $xtoken = PmcTestLogic::GenerarTokenSeguridad();
        $curl = curl_init();

        if ($xtoken != "") {

            $options = array(
                CURLOPT_URL => $urlbase . "webservice/Cases/RecordsList",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 100,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "x-api-key:" . $xapikey,
                    "x-token:" . $xtoken,
                    "x-row-order:" . "DESC",
                    "x-row-limit:" . "500",
                    "x-row-order-field:" . "modifiedtime"
                ],
                CURLOPT_USERPWD => "$username:$password",
                CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

            );

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt_array($curl, $options);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            $result = json_decode($response, true);

            if (!General::isEmpty($result)) {

                $newrecords = array();
                foreach ($result['result']['records'] as $k => $v) {
                    $newrecords[] = array(
                        'recordid'                        => $k
                    );
                }

                return $newrecords;
            }

            if ($err) {
                $response = "CURL Error #:" . $err;
            }
        } else {
            return [[], "Error al Generar Token PMC", "error"];
        }
    }

    public static function ClaimsCollectionsListToUpdate()
    {
        $username = env('CURLOPT_USER');
        $password = env('CURLOPT_PWD');
        $xapikey = env('X_API_KEY');
        $urlbase = env('URL_BASE');
        $username_post_fieds = env('CURLOPT_POSTFIELDS_NAME');
        $password_post_fieds = env('CURLOPT_POSTFIELDS_PASSWORD');

        $xtoken = PmcTestLogic::GenerarTokenSeguridad();
        $curl = curl_init();

        if ($xtoken != "") {

            $options = array(
                CURLOPT_URL => $urlbase . "webservice/ClaimCollections/RecordsList",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 100,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "x-api-key:" . $xapikey,
                    "x-token:" . $xtoken,
                    "x-row-order:" . "DESC",
                    "x-row-limit:" . "500",
                    "x-row-order-field:" . "modifiedtime"
                ],
                CURLOPT_USERPWD => "$username:$password",
                CURLOPT_POSTFIELDS => "userName=" . $username_post_fieds . "&password=" . $password_post_fieds

            );

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt_array($curl, $options);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            $result = json_decode($response, true);

            if (!General::isEmpty($result)) {

                $newrecords = array();
                foreach ($result['result']['records'] as $k => $v) {
                    $newrecords[] = array(
                        'recordid'                        => $k
                    );
                }

                return $newrecords;
            }

            if ($err) {
                $response = "CURL Error #:" . $err;
            }
        } else {
            return [[], "Error al Generar Token PMC", "error"];
        }
    }
}
