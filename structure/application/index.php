<?php
use PHPMailer\PHPMailer\PHPMailer;

session_start();
session_id($_REQUEST['sessionId'] ?? null);
error_reporting(E_ALL);
ini_set('display_errors', '1');
$currentRequestTime = time();

function getCurrentRequestTime()
{
    global $currentRequestTime;
    return $currentRequestTime;
}

function getCurrentRequestTimeStamp()
{
    return date("YmdHis", getCurrentRequestTimeStamp(getCurrentRequestTime()));
}

function getFunctionName($function)
{
    if (is_callable($function)) {
        $reflection = new ReflectionFunction($function);
        return $reflection->getName();
    } else {
        return null;
    }
}

function getCurrentRequestUrl()
{
    return sprintf('%s://%s%s', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
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
    return getParametersByMethod($method);
}

function getParametersByMethod($method)
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
            return [];
    }
}



function isProductionEnvironment()
{
    $productionDomains = [
        'www.farolfi.it',
        'staging.farolfi.it',
        'farolfi.it'
    ];
    $host = $_SERVER['HTTP_HOST'];
    $isProd = in_array($host, $productionDomains);
    return $isProd;
}



function appendFooterScript($fileOrCode)
{
    global $footer_script;
    if (! array_search_case_insensitive($fileOrCode, $footer_script)) {
        $footer_script[] = $fileOrCode;
    }
}

function isPathOrUrl($string)
{
    $isPath = preg_match('/^(\/|\.\/|\.\.\/|[a-zA-Z]:\\|\\{1,2}){0,1}(\w+[\\/]{1})+$/', $string);
    $isUrl = filter_var($string, FILTER_VALIDATE_URL) !== false;
    return $isPath || $isUrl;
}

function showFooterScript()
{
    global $footer_script,$scriptCacheTimestamp;
    foreach ($footer_script as $script) {
        echo '<script type="text/javascript" ';
        if (isPathOrUrl($script))
            echo 'src="' . $script . '?'.$scriptCacheTimestamp.'"></script>';
            else
                echo '>' . $script . '</script>';
    }
}

function showMessage($level, $content, $variables = [])
{
    ?><script> showMessage(<?= json_encode($level)?>,<?= json_encode($content)?>,<?= json_encode($variables)?>); </script><?php
}

function includeGadgets()
{
    $phpFiles = glob('gadgets/gadget-*.php');
    foreach ($phpFiles as $phpFile) {
        include_once $phpFile;
    }
}

function includeAllCssInDirectory($directory)
{
    $cssFiles = glob('css/' . $directory . '/*.css');

    foreach ($cssFiles as $cssFile) {
        echo '<link rel="stylesheet" type="text/css" href="' . $cssFile . '">';
    }
}

function includeAllJavaScriptInDirectory($directory)
{
    global $scriptCacheTimestamp; 
    $scripts = glob('js/' . $directory . '/*.js'); 

    foreach ($scripts as $script) {
        echo '<script type="text/javascript" src="' . $script . '?'.$scriptCacheTimestamp.'"></script>';
    }
}

function serializeJson(array $array, $file)
{
    $json = json_encode($array, JSON_PRETTY_PRINT);
    file_put_contents($file, $json);
    reloadSiteData();
}

function readJson($file)
{
    $content = file_get_contents($file);
    $arrayAssociativo = json_decode($content, true);
    return $arrayAssociativo;
}

function getSiteData(string $name)
{
    global $siteData;
    return $siteData[$name] = $siteData[$name] ?? readJson(__DIR__ . '/../data/' . $name . '.json');
}

function reloadSiteData()
{
    global $siteData;
    $siteData = [];
    $jsonFiles = glob(__DIR__ . '/../data/*.json');
    foreach ($jsonFiles as $jsonFile) {
        getSiteData(pathinfo($jsonFile, PATHINFO_FILENAME));
    }
}

function showAsScript()
{
    isolateResponseAs("application/javascript", function () {
        echo implode('', array_map(function ($api) {
            return $api ? showFunctionAsScript($api['function'], $api['method']) : '';
        }, getFunctionsWithApiAnnotation()));
    });
}
function getFunctionsStartingWith(string $prefix)
{
    $functions = get_defined_functions()['user'];
    $selecterFunctions = array_filter($functions, function ($function) use ($prefix) {
        return strpos($function, $prefix) === 0;
    });
        return $selecterFunctions;
}
function isolateResponseAs($contentType, $contentCallBack)
{
    ob_clean();
    header("Content-Type: $contentType");
    $contentCallBack();
    die();
}

function getFunctionsWithApiAnnotation()
{
    $apis = [];
    foreach (get_defined_functions()['user'] as $function) {
        $apis[] = isApiFunction($function);
    }
    return $apis;
}

function throwApiException($f)
{
    throw new ErrorException("Can not invoke api function '$f'!");
}

function invokeFunctionFromRequest($functionName, $method, $onError = "throwApiException")
{
    $params = getParametersByMethod($method);
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

function isApiFunction($function)
{
    $functionReflection = new ReflectionFunction($function);
    $docComment = $functionReflection->getDocComment();
    if (preg_match('/@api\s+(GET|POST|UPDATE|DELETE|PATCH)/i', $docComment, $matches)) {
        $ctr = preg_match('/@content-type\s+([\S]+)/i', $docComment, $ct) ? $ct[1] : 'application/json';
        return [
            "function" => $functionReflection,
            "method" => strtoupper($matches[1]),
            "content-type" => $ctr
        ];
    }
    return false;
}

function showFunctionAsScript(ReflectionFunction $function, string $httpmethod = "GET")
{
    $mp = $function->getParameters();
    $p = implode(' ', array_map(function ($arg) {
        return ', ' . $arg->getName();
    }, $mp));
    $pMap = '{"sessionId": "' . session_id() . '"';
    foreach ($mp as $mpi) {
        $pMap .= ',"' . $mpi->getName() . '":' . $mpi->getName() . '';
    }
    $pMap .= '}';
    $js = 'function ' . $function->getName() . '(callback' . $p . ',caller=null){ ';
    $js .= 'sendRequest(callback,' . json_encode(getCurrentUrlWithoutFragmentAndQuery()) . ', "' . $function->getName() . '", "' . $httpmethod . '",' . $pMap . ');}';
    return $js;
}




//HTTP ERRORS
function sendError100()
{
    http_response_code(100);
    die('Continue');
}

function sendError200()
{
    http_response_code(200);
    die('OK');
}

function sendError201()
{
    http_response_code(201);
    die('Created');
}

function sendError204()
{
    http_response_code(204);
    die('No Content');
}

function sendError301()
{
    http_response_code(301);
    die('Moved Permanently');
}

function sendError302()
{
    http_response_code(302);
    die('Found (or Moved Temporarily)');
}

function sendError304()
{
    http_response_code(304);
    die('Not Modified');
}

function sendError400()
{
    http_response_code(400);
    die('Bad Request');
}

function sendError401()
{
    http_response_code(401);
    die('Unauthorized');
}

function sendError403()
{
    http_response_code(403);
    die('Forbidden');
}

function sendError404()
{
    http_response_code(404);
    die('Not Found');
}

function sendError500()
{
    http_response_code(500);
    die('Internal Server Error');
}

function sendError502()
{
    http_response_code(502);
    die('Bad Gateway');
}

function sendError503()
{
    http_response_code(503);
    die('Service Unavailable');
}


}