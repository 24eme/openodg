<?php

class ConditionnementSecurity extends DocumentSecurity implements SecurityInterface {

    protected $doc;
    protected $user;

    public static function getInstance($user, $doc) {

        return new ConditionnementSecurity($user, $doc);
    }

    public function isAdmin() {

	return $this->user->hasConditionnementAdmin() || parent::isAdmin();
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }

        if(($this->user->isAdmin() || ($this->user->hasConditionnementAdmin()))){
            return true;
        }

        $authorized = parent::isAuthorized($droits);

        if(!$authorized) {

            return false;
        }

        return true;
    }

}
