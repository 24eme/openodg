<?php

class HabilitationSecurity extends DocumentSecurity implements SecurityInterface {

    protected $doc;
    protected $user;

    public static function getInstance($user, $doc) {

        return new HabilitationSecurity($user, $doc);
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }

        if(!$this->user->isAdmin() && $this->user->getEtablissement()->identifiant != $this->doc->identifiant) {

            $lienSymbolique = DeclarationClient::getInstance()->find(str_replace($this->doc->identifiant, $this->user->getEtablissement()->identifiant, $this->doc->_id), acCouchdbClient::HYDRATE_JSON, true);

            if(!$lienSymbolique || $lienSymbolique->type != "LS") {
                return false;
            }

            if($lienSymbolique->pointeur != $this->doc->_id) {

                return false;
            }
        }

        if(!$authorized) {

            return false;
        }

        return true;
    }

}
