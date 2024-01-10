<?php
function getFunctionName($function)
{
    if (is_callable($function)) {
        $reflection = new ReflectionFunction($function);
        return $reflection->getName();
    } else {
        return null;
    }
}
function getFunctionsStartingWith(string $prefix)
{
    $functions = get_defined_functions()['user'];
    $selecterFunctions = array_filter($functions, function ($function) use ($prefix) {
        return strpos($function, $prefix) === 0;
    });
        return $selecterFunctions;
}
