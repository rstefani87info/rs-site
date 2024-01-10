<?php
session_start();
session_id($_REQUEST['sessionId'] ?? null);
error_reporting(E_ALL);
ini_set('display_errors', '1');
$currentRequestTime = time();
$siteData = [];
define('PROD_DOMAINS', [
    'www.farolfi.it',
    'staging.farolfi.it',
    'farolfi.it'
]);

header('Content-Type: text/html; charset=utf8mb4');

function getCurrentRequestTime()
{
    global $currentRequestTime;
    return $currentRequestTime;
}

function getCurrentRequestTimeStamp()
{
    return date("YmdHis", getCurrentRequestTimeStamp(getCurrentRequestTime()));
}

function getCurrentRequestUrl()
{
    return sprintf('%s://%s%s', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
}

function isProductionEnvironment()
{
    
    $host = $_SERVER['HTTP_HOST'];
    $isProd = in_array($host, PROD_DOMAINS);
    return $isProd;
}

function getCurrentUrlWithoutFragmentAndQuery()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = strtok($_SERVER['REQUEST_URI'], '?');
    $url = $protocol . '://' . $host . $path;
    return $url;
}

function getCurrentFragment()
{
    $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $urlParts = parse_url($url);
    return isset($urlParts['fragment']) ? $urlParts['fragment'] : null;
}

function getParametersByThisRequestMethod()
{
    $method = $_SERVER['REQUEST_METHOD'];
    return getParametersByRequestMethod($method);
}

function getParametersByRequestMethod($method)
{
    switch ($method) {
        case 'GET':
            return $_GET;
        case 'POST':
            return array_merge($_POST, $_FILES);
        case 'FILE':
            return $_FILES;
        case 'PUT':
        case 'PATCH':
        case 'DELETE':
            parse_str(file_get_contents('php://input'), $params);
            return $params;
        default:
            return $_REQUEST;
    }
}

function showMessage($level, $content, $variables = [])
{
    ?><script> showMessage(<?= json_encode($level)?>,<?= json_encode($content)?>,<?= json_encode($variables)?>); </script><?php
}

function includeGadgets()
{
    $phpFiles = glob(__DIR__ . '/../*/index.php');
    foreach ($phpFiles as $phpFile) {
        include_once $phpFile;
    }
}

function reloadSiteData()
{
    $jsonFiles = glob(__DIR__ . '/../data/*.json');
    foreach ($jsonFiles as $jsonFile) {
        getSiteData(pathinfo($jsonFile, PATHINFO_FILENAME));
    }
}

function isolateResponseAs($contentType, $contentCallBack)
{
    ob_clean();
    header("Content-Type: $contentType");
    $contentCallBack();
    die();
}

function invokeFunctionFromRequest($functionName, $method, $onError = "throwApiException")
{
    $params = getParametersByRequestMethod($method);
    if (function_exists($functionName)) {
        $reflectionFunction = new ReflectionFunction($functionName);
        $parameters = $reflectionFunction->getParameters();
        $arguments = [];
        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();
            if (isset($params[$parameterName])) {
                $arguments[] = $params[$parameterName];
            } else {
                $arguments[] = null;
            }
        }
        $result = $reflectionFunction->invokeArgs($arguments);
        return $result;
    } else {
        $onError($functionName);
    }
}









