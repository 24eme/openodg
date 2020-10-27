<?php

class Phone {

    public static function clean($numero) {
        $numero = preg_replace('/^\+[0-9]{2}/', '0', $numero);
        $numero = preg_replace('/[^0-9]+/', '', $numero);

        if(!$numero) {

            return null;
        }

        return $numero;
    }

    public static function format($numero) {
        $numero = self::clean($numero);

        if(is_null($numero)) {

            return null;
        }

        return trim(preg_replace("/([0-9]{2})/", "$1 ", $numero));
    }

}
