<?php

session_start();
session_id($_GET['sessionId']??null);
error_reporting(E_ALL);
ini_set('display_errors', '1');
$scriptCacheTimestamp=date("YmdHis", time());
include __DIR__ . '/functions.php';
ini_set('error_log', __DIR__ .'../log/error_log.txt');
ini_set('log_errors', 1);
includeGadgets();




$siteData = [];
$footer_script = [];
$apiName = $_REQUEST['@api'] ?? '';

if (!empty($apiName)) {
    if (preg_match('/^js|javascript|jscript|script$/i', $apiName)) {
        showAsScript();
    } else  {
        $api = isApiFunction($apiName);
        isolateResponseAs($api['content-type'] ?? "application/json; charset=utf8mb4", function () use ($api, $apiName) {
            if ($api){
                $res=invokeFunctionFromRequest($apiName, $api['method']);
                echo preg_match('/[a-z]ml/i',$api['content-type'])?$res:json_encode($res);
            }else
                sendError403();
        });
    } 
}
header('Content-Type: text/html; charset=utf8mb4');



?>