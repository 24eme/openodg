<?php

class Anonymization {

    public static function hideIfNeeded($s) {
        return preg_replace('/[^ @\.]/', 'X', $s);
    }

}
