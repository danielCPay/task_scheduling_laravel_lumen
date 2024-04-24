<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Cast\Object_;
use PhpParser\Node\Stmt\Return_;

class PmcTestModel
{

    public static function ToRegisterUpdateCab(array $data): Object
    {
        $parametros = array(
            $data['recordscab']
        );

        $res = DB::select("CALL sp_records_cab(?)", $parametros);
        return $res[0] ?? null;
    }
    public static function ToRegisterUpdateDet(array $data): Object
    {
        $parametros = array(
            $data['recordid'],
            $data['recordsdet']
        );

        $res = DB::select("CALL sp_records_det(?,?)", $parametros);
        return $res[0] ?? null;
    }
    public static function ToRegisterDet(array $data): Object
    {
        $parametros = array(
            $data['recordid'],
            $data['recordsdet']
        );

        $res = DB::select("CALL sp_insert_records_det(?,?)", $parametros);
        return $res[0] ?? null;
    }
    public static function ToUpdateDet(array $data): Object
    {
        $parametros = array(
            $data['recordid'],
            $data['recordsdet']
        );

        $res = DB::select("CALL sp_update_records_det(?,?)", $parametros);
        return $res[0] ?? null;
    }
    public static function ToListRecordsCab(): array
    {
        $res = DB::select("CALL sp_list_recordscab()");
        return $res;
    }
    public static function ToListRecordsCabAll(): array
    {
        $res = DB::select("CALL sp_list_recordscab_all()");
        return $res;
    }
    public static function ProbandoTabla(): array
    {

        $sql = "CREATE TABLE records (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            recordscode INT NOT NULL       
            )";


        $qi = "INSERT INTO records (recordscode";
        $qi .= ") VALUES (";

        $j_obj = array();

        foreach ($j_obj as $j_arr_key => $value) {
            $qi .= "'" . $value . "',";
        }
        $qi .= ")";


        $users = DB::select($sql);
        $users = DB::select($qi);
        return $users;
    }
    public static function ValidateToken($currentdate): Object
    {
        $parametros = array(
            $currentdate
        );
        $res = DB::select("CALL sp_validate_token_current_date(?)", $parametros);
        return $res[0] ?? new \stdClass();
    }

    public static function InsertToken(array $datos): Object
    {
        $parametros = array(
            $datos['token']
        );
        $res = DB::select("CALL sp_to_register_update_token(?)", $parametros);
        return $res[0] ?? null;
    }

    public static function RecordsCabCount(): Object
    {
        $res = DB::select("CALL sp_records_cab_count()");
        return $res[0] ?? new \stdClass();
    }

    public static function RecordsCabClaimsCount(): Object
    {
        $res = DB::select("CALL sp_records_cab_claims_count()");
        return $res[0] ?? new \stdClass();
    }

    public static function RecordsCabClaimsCollectionCount():Object
    {
        $res= DB::select("CALL sp_records_cab_claims_collection_count()");
        return $res[0] ?? new \stdClass();
    }

    public static function ExecuteProcessDataAuditLog(
        $p_number_of_pre_process_records,
        $p_number_of_post_process_records,
        $p_number_of_new_process_records,
        $p_start_time_process,
        $p_end_time_process,
        $process,
        $module
    ): Object {

        $parametros = array(
            $p_number_of_pre_process_records,
            $p_number_of_post_process_records,
            $p_number_of_new_process_records,
            $p_start_time_process,
            $p_end_time_process,
            $process,
            $module
        );

        $res = DB::select("CALL execute_process_data_audit_log(?,?,?,?,?,?,?)", $parametros);

        return $res[0] ?? new \stdClass();
    }
    public static function InsertRecordsCabClaims(array $data): Object
    {
        $parametros = array(
            $data['recordscab']
        );

        $res = DB::select("CALL sp_insert_recordscab_claims(?)", $parametros);
        return $res[0] ?? null;
    }
    public static function InsertRecordsDetClaims(array $data): Object
    {
        $parametros = array(
            $data['recordid'],
            $data['recordsdet']
        );

        $res = DB::select("CALL sp_insert_recordsdet_claims(?,?)", $parametros);       
        return $res[0] ?? null;
    }
    public static function UpdateRecordsDetClaims(array $data): Object
    {
        $parametros = array(
            $data['recordid'],
            $data['recordsdet']
        );

        $res = DB::select("CALL sp_update_records_det_claims(?,?)", $parametros);       
        return $res[0] ?? null;
    }
    public static function InsertRecordsCabClaimsCollections(array $data): Object
    {
        $parametros = array(
            $data['recordscab']
        );

        $res = DB::select("CALL sp_insert_recordscab_claims_collections(?)", $parametros);
        return $res[0] ?? null;
    }
    public static function InsertRecordsDetClaimsCollections(array $data): Object
    {
        $parametros = array(
            $data['recordid'],
            $data['recordsdet']
        );

        $res = DB::select("CALL sp_insert_recordsdet_claim_collections(?,?)", $parametros);       
        return $res[0] ?? null;
    }
    public static function UpdateRecordsDetClaimsCollections(array $data): Object
    {
        $parametros = array(
            $data['recordid'],
            $data['recordsdet']
        );

        $res = DB::select("CALL sp_update_records_det_claims_collections(?,?)", $parametros);       
        return $res[0] ?? null;
    }
    
    public static function ToListRecordsCabClaims(): array
    {
        $res = DB::select("CALL sp_list_recordscab_claims()");
        return $res;
    }
    public static function ToListRecordsCabClaimsAll(): array
    {
        $res = DB::select("CALL sp_list_recordscab_claims_all()");
        return $res;
    }
    public static function ToListRecordsCabClaimsCollectionsAll(): array
    {
        $res = DB::select("CALL sp_list_recordscab_claims_collections_all()");
        return $res;
    }
    public static function ToListRecordsCabClaimsCollections(): array
    {
        $res = DB::select("CALL sp_list_recordscab_claims_collections()");
        return $res;
    }
}
