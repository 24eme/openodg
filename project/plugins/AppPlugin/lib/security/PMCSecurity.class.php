<?php

class PMCSecurity extends DocumentSecurity implements SecurityInterface {

    protected static $_instance;

    protected $doc;
    protected $user;

    public static function getInstance($user, $doc) {
        if ( ! isset(self::$_instance)) {
            self::$_instance = new self($user, $doc);
        }
        return self::$_instance;
    }

    public function isAdmin() {

	return $this->user->hasPMCAdmin() || parent::isAdmin();
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }

        if(($this->user->isAdmin() || ($this->user->hasPMCAdmin()))){
            return true;
        }

        $authorized = parent::isAuthorized($droits);

        if(!$authorized) {

            return false;
        }

        return true;
    }

}
