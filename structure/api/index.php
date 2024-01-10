<?php

$apiName = $_REQUEST['@api'] ?? '';

if (! empty($apiName)) {
     
        $api = isApiFunction($apiName);
        isolateResponseAs($api['content-type'] ?? "application/json; charset=utf8mb4", function () use ($api, $apiName) {
            if ($api) {
                $res = invokeFunctionFromRequest($apiName, $api['method']);
                echo preg_match('/[a-z]ml/i', $api['content-type']) ? $res : json_encode($res);
            } else
                sendError403();
        });
  
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