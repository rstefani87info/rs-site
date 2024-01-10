<?php
function serializeJson(array $array, $file)
{
    $json = json_encode($array, JSON_PRETTY_PRINT);
    file_put_contents($file, $json);
}

function readJson($file)
{
    $content = file_get_contents($file);
    $arrayAssociativo = json_decode($content, true);
    return $arrayAssociativo;
}