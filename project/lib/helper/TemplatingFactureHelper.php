<?php

function formatFloat($number, $decimalSeparator = ".") {
    $arrondi = round($number, 2);
    if ($number == $arrondi) {
    	return number_format($number, 2, $decimalSeparator, ' ');
    } else {
    	return number_format($number, 3, $decimalSeparator, ' ');
    }
}

function formatQuantite($number, $decimalSeparator = ".") {
	$find = preg_match('/[0-9]+[,.]([0-9]*)/', $number, $matches);
	$nb = 0;
	if ($find) {
		$nb = strlen($matches[1]);
	}
	return number_format($number, $nb, $decimalSeparator, ' ');
}
