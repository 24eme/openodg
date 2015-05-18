<?php

function formatFloat($number) {
    $decimal = (strrpos($number, '.'))? substr($number, strpos($number, '.')+1) : 0;
    return ($decimal > 2)? $number : sprintf("%01.02f", $number);
}
