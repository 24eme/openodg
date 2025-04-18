<?php

class ParcellaireSecurity extends DocumentSecurity implements SecurityInterface {

    protected $doc;
    protected $user;

    public static function getInstance($user, $doc) {

        return new ParcellaireSecurity($user, $doc);
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }
        if($this->doc->getEtablissementObject()->exist("liaisons_operateurs")){
            foreach($this->doc->getEtablissementObject()->liaisons_operateurs as $k => $l) {
                if (strpos($k, $this->user->getCompte()->identifiant) !== false) {
                    return true;
                }
            }
        }

        $authorized = parent::isAuthorized($droits);
        if(!$authorized) {

            return false;
        }

        return true;
    }

}