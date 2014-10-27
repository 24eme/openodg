<?php

class EtablissementSecurity implements SecurityInterface {

    const DECLARANT_DREV = 'DECLARANT_DREV';

    protected $user;
    protected $etablissement;

    public static function getInstance($user, $etablissement) {

        return new EtablissementSecurity($user, $etablissement);
    }

    public function __construct($user, $etablissement) {
        $this->user = $user;
        $this->etablissement = $etablissement;
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }

        /*** DECLARANT ***/

        if(!$this->user->isAdmin() && $this->user->getEtablissement()->_id != $this->etablissement->_id) {

            return false;
        }

        if(in_array(self::DECLARANT_DREV, $droits) && !$this->etablissement->hasFamille(EtablissementClient::FAMILLE_VINIFICATEUR)) {

            return false;
        }

        return true;
    }

}