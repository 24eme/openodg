<?php

abstract class DocumentSecurity implements SecurityInterface {

    const EDITION = 'EDITION';
    const VALIDATION_ADMIN = 'VALIDATION_ADMIN';
    const VISUALISATION = 'VISUALISATION';
    const PDF = 'PDF';
    const DEVALIDATION = 'DEVALIDATION';

    protected $doc;
    protected $user;
    protected $etablissement;

    public function __construct($user, $doc = null) {
        $this->user = $user;
        $this->doc = $doc;
    }

    public function isAdminODG() {
	     return $this->user->isAdminODG() && $this->user->hasDrevAdmin();
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }

        if(($this->user->isAdminODG() || ($this->user->hasDrevAdmin()))){
            return true;
        }

        if(in_array(self::PDF, $droits)) {

            return true;
        }

        if (in_array(self::EDITION, $droits) || in_array(self::VISUALISATION, $droits)) {
            if($this->user->getCompte() && $this->user->getCompte()->getSociete() && preg_match("/^".$this->user->getCompte()->getSociete()->identifiant."/", $this->doc->identifiant)) {
                return true;
            }
        }

        if (in_array(self::VISUALISATION, $droits)) {
            if ($this->user->getEtablissement()->hasCooperateur($this->doc->declarant->cvi)) {
                return true;
            }
        }

        if(!$this->isAdminODG() && $this->user->getCompte() && !preg_match("/^".$this->user->getCompte()->identifiant."/", $this->doc->identifiant)) {

            $lienSymbolique = null;

            if($this->user->getCompte()->getSociete() && $this->user->getCompte()->getSociete()->getEtablissementPrincipal()) {
                $lienSymbolique = DeclarationClient::getInstance()->find(str_replace($this->doc->identifiant, $this->user->getCompte()->getSociete()->getEtablissementPrincipal()->identifiant, $this->doc->_id), acCouchdbClient::HYDRATE_JSON, true);
            }

            if(!$lienSymbolique || $lienSymbolique->type != "LS") {
                return false;
            }

            if($lienSymbolique->pointeur != $this->doc->_id) {

                return false;
            }
        }

        if(in_array(self::EDITION, $droits) && $this->doc->validation) {

            return false;
        }

        if(in_array(self::EDITION, $droits) && $this->doc->isPapier() && !$this->user->isAdminODG() && !$this->user->hasDrevAdmin()) {

            return false;
        }

        if(in_array(self::EDITION, $droits) && $this->doc->isAutomatique() && !$this->user->isAdminODG() && !$this->user->hasDrevAdmin()) {

            return false;
        }

        if(in_array(self::VALIDATION_ADMIN, $droits) && !$this->user->isAdminODG() && !$this->user->hasDrevAdmin()) {

            return false;
        }

        if(in_array(self::VALIDATION_ADMIN, $droits) && $this->doc->validation_odg) {

            return false;
        }

        if(in_array(self::DEVALIDATION, $droits) && $this->doc->isLectureSeule()) {

            return false;
        }

        if(in_array(self::DEVALIDATION, $droits) && !$this->user->isAdminODG() && !$this->user->hasDrevAdmin()) {

            return false;
        }

        if(in_array(self::DEVALIDATION, $droits) && $this->doc instanceof InterfaceMouvementFacturesDocument && !$this->doc->isNonFactures()) {

            return false;
        }

        return true;
    }

}
