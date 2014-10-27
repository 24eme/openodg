<?php

abstract class DocumentSecurity implements SecurityInterface {

    const EDITION = 'EDITION';

    protected $doc;
    protected $user;
    protected $etablissement;

    public function __construct($user, $doc = null) {
        $this->user = $user;
        $this->doc = $doc;
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }

        if(!$this->user->isAdmin() && $this->user->getEtablissement()->identifiant != $this->doc->identifiant) {

            return false;
        }

        /*if(in_array(self::EDITION, $droits) && $this->doc->validation) {

            return false;
        }*/

        if(in_array(self::EDITION, $droits)) {

            return true;
        }

        return true;
    }

}