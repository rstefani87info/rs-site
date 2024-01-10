<?php
$jsMode=array_key_exists('@jsmode',$_REQUEST);
if ($jsMode) {
    showAsScript();
} 


$footer_script = [];
function appendFooterScript($fileOrCode)
{
    global $footer_script;
    if (! array_search_case_insensitive($fileOrCode, $footer_script)) {
        $footer_script[] = $fileOrCode;
    }
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
function showAsScript()
{
    isolateResponseAs("application/javascript", function () {
        echo implode('', array_map(function ($api) {
            return $api ? showFunctionAsScript($api['function'], $api['method']) : '';
        }, getFunctionsWithApiAnnotation()));
    });
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

function includeAllJavaScriptInDirectory($directory)
{
    global $scriptCacheTimestamp;
    $scripts = glob('js/' . $directory . '/*.js');
    
    foreach ($scripts as $script) {
        echo '<script type="text/javascript" src="' . $script . '?'.$scriptCacheTimestamp.'"></script>';
    }
}