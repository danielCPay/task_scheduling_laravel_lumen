<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$router->get('/', function () use ($router) {
    return $router->app->version();
});
//pmc test
$router->post('/pmc/generatetokensegurity', 'PmcTestController@GenerarTokenSeguridad');
$router->post('/pmc/recordslists', 'PmcTestController@CasesRecordsLists');
$router->post('/pmc/casewithrecordid', 'PmcTestController@GetCaseWithRecordId');
$router->post('/pmc/probandotabla', 'PmcTestController@ProbandoTabla');
$router->post('/pmc/insertrecordsdet', 'PmcTestController@InsertAllCaseWithRecordId');
$router->post('/pmc/updaterecordsdet', 'PmcTestController@UpdateAllCaseWithRecordId');
//////////CLAIMS///////////////////
$router->post('/claims/insertcab', 'PmcTestController@InsertRecordsCabClaims');
$router->post('/claims/insertdet', 'PmcTestController@InsertRecordsDetClaims');
$router->put('/claims/updatedet', 'PmcTestController@UpdateRecordsDetClaims');
//////////CLAIMS COLLECTIONS///////////////////
$router->post('/claimscollections/insertcab', 'PmcTestController@InsertRecordsCabClaimsCollections');
$router->post('/claimscollections/insertdet', 'PmcTestController@InsertRecordsDetClaimsCollections');
$router->put('/claimscollections/updatedet', 'PmcTestController@UpdateRecordsDetClaimsCollections');