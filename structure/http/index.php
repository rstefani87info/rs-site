<?php


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