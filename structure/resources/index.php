<?php
function isPathOrUrl($string)
{
    $isPath = preg_match('/^(\/|\.\/|\.\.\/|[a-zA-Z]:\\|\\{1,2}){0,1}(\w+[\\/]{1})+$/', $string);
    $isUrl = filter_var($string, FILTER_VALIDATE_URL) !== false;
    return $isPath || $isUrl;
}