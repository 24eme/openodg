<?php

function formatFloat($number, $defaultDecimalFormat = null, $maxDecimalAuthorized = null, $format = null, $milliSeparate = false) {

    return FloatHelper::getInstance()->format($number, $defaultDecimalFormat, $maxDecimalAuthorized, $format, $milliSeparate);
}

function formatFloatFr($number, $defaultDecimalFormat = null, $maxDecimalAuthorized = null, $format = null, $milliSeparate = false) {

    return FloatHelper::getInstance()->formatFr($number, $defaultDecimalFormat, $maxDecimalAuthorized, $format, $milliSeparate);
}

function sprintFloat($number, $format = "%01.02f", $milliSeparate = false)
{
    return formatFloat($number, null, null, $format, $milliSeparate);
}

function sprintFloatFr($float, $format = "%01.02f", $milliSeparate = false)
{
    return formatFloatFr($float, null, null, $format, $milliSeparate);
}

function echoFloat($number, int $nbDecimales = 2, bool $milliSeparate = true)
{
    echo formatFloat($number, $nbDecimales, null, null, $milliSeparate);
}

function echoFloatFr($number, int $nbDecimales = 2, bool $milliSeparate = true)
{
    echo formatFloatFr($number, $nbDecimales, null, null, $milliSeparate);
}

function echoLongFloat($number, $milliSeparate = false)
{
    echo formatFloat($number, 4, 4, null, $milliSeparate);
}

function echoLongFloatFr($number, $milliSeparate = false)
{
    echo formatFloatFr($number, 4, 4, null, $milliSeparate);
}

function echoSignedFloat($number)
{
    echo ($number>0)? '+'.formatFloat($number) : formatFloat($number);
}

function echoArialFloat($number) {

    echo number_format($number, 2, '.', ' ');
}

function getArialFloat($number) {

    return number_format($number, 2, '.', ' ');
}

function sprintInputFloat($float, $format = "%01.02f") {
    if (is_null($float) || $float === "")
        return null;
    return sprintFloat($float, $format);
}
function echoSuperficie($s) {
    if (ParcellaireConfiguration::getInstance()->isAres()) {
        echo formatFloatFr($s * 100, 2, 2);
        return ;
    }
    echo formatFloatFr($s, 4, 4);
}
