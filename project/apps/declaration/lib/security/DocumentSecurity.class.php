<?php

abstract class DocumentSecurity implements SecurityInterface {

    const EDITION = 'EDITION';
    const VALIDATION_ADMIN = 'VALIDATION_ADMIN';
    const VISUALISATION = 'VISUALISATION';
    const DEVALIDATION = 'DEVALIDATION';

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

        if(in_array(self::EDITION, $droits) && $this->doc->validation) {

            return false;
        }

        if(in_array(self::EDITION, $droits) && $this->doc->isPapier() && !$this->user->isAdmin()) {

            return false;
        }

        if(in_array(self::EDITION, $droits) && $this->doc->isAutomatique() && !$this->user->isAdmin()) {

            return false;
        }

        if(in_array(self::VALIDATION_ADMIN, $droits) && !$this->user->isAdmin()) {

            return false;
        }

        if(in_array(self::VALIDATION_ADMIN, $droits) && $this->doc->validation_odg) {

            return false;
        }

        if(in_array(self::DEVALIDATION, $droits) && !$this->user->isAdmin()) {

            return false;
        }

        if(in_array(self::DEVALIDATION, $droits) && $this->doc instanceof InterfaceMouvementDocument && !$this->doc->isNonFactures()) {

            return false;
        }

        return true;
    }

}