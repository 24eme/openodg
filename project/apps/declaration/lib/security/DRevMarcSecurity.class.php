<?php

class DRevMarcSecurity extends DocumentSecurity implements SecurityInterface {

    protected $doc;
    protected $user;

    public static function getInstance($user, $doc) {

        return new DRevMarcSecurity($user, $doc);
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }

        $authorized = parent::isAuthorized($droits);
    
        if(!$authorized) {

            return false;
        }

        if(in_array(self::VALIDATION_ADMIN, $droits)) {

            return false;
        }

        return true;
    }

}