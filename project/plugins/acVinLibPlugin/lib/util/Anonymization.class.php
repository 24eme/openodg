<?php

class Anonymization {

    public static function needToHide() {
        return getenv('ANONYMIZATION_HIDE');
    }

    public static function hideIfNeeded($s) {
        if (self::needToHide()) {
            return preg_replace('/[^ @\.]/', 'X', $s);
        }else{
            return $s;
        }
    }

}
