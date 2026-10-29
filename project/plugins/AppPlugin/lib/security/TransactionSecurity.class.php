<?php

class TransactionSecurity extends DocumentSecurity implements SecurityInterface {

    protected $doc;
    protected $user;

    public static function getInstance($user, $doc) {

        return new TransactionSecurity($user, $doc);
    }

    public function isAdmin() {

	return $this->user->hasTransactionAdmin() || parent::isAdmin();
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }

        if(($this->user->isAdmin() || ($this->user->hasTransactionAdmin()))){
            return true;
        }

        $authorized = parent::isAuthorized($droits);

        if(!$authorized) {

            return false;
        }

        return true;
    }

}
