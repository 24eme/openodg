<?php

class AdelpheSecurity extends DocumentSecurity implements SecurityInterface {

    protected $doc;
    protected $user;
    const DROIT_ADELPHE = "adelphe";

    public static function getInstance($user, $doc) {
      return new AdelpheSecurity($user, $doc);
    }

    public function isAuthorized($droits) {
      if(!is_array($droits)) {
        $droits = array($droits);
      }
      $authorized = parent::isAuthorized($droits);
      if(!$authorized) {
        return false;
      }
      return true;
    }
}
