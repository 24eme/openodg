<?php

class Phone {

    public static function clean($numero) {
        $numero = preg_replace('/^\+[0-9]{2}/', '0', $numero);
        $numero = preg_replace('/[^0-9]+/', '', $numero);

        return $numero;
    }

    public static function format($numero) {

        return trim(preg_replace("/([0-9]{2})/", "$1 ", self::clean($numero)));
    }

}
