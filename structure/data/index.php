<?php

//dates
function convertFormW3ToMySQLDateTime(string $date): string
{
    return str_replace('T', ' ', trim($date));
}

//arrays
function isAssociativeArray($array)
{
    return count(array_filter(array_keys($array), 'is_string')) > 0;
}

//array of string
function array_search_case_insensitive($needle, $haystack)
{
    if ($haystack && ! empty($haystack)) {
        foreach ($haystack as $key => $value) {
            if (strcasecmp($needle, $value) === 0) {
                return $key;
            }
        }
    }
    return false;
}

//numbers
function getNumberSequence($min, $max, $step)
{
    $arr = [];
    for ($i = $min; $i <= $max; $i += $step) {
        $arr[] = $i;
    }
    return $arr;
}

function formatIntToNDigits($number, $digitNumber)
{
    $realNumber = (int) $number;
    $realDigitNumber = isset($digitNumber) ? (int) $digitNumber : 1;
    
    if ($realDigitNumber <= strlen((string) $number)) {
        return $realNumber;
    } else {
        return '0' . formatIntToNDigits($number, $realDigitNumber - 1);
    }
}