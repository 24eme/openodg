<?php

class AppUser extends sfBasicSecurityUser {

    const SESSION_COMPTE_LOGIN = "COMPTE_LOGIN";
    const SESSION_COMPTE_DOC = "COMPTE_DOC_ID";
    const SESSION_USURPATION_URL_BACK = "USURPATION_URL_BACK";
    const NAMESPACE_COMPTE = "COMPTE";
    const NAMESPACE_COMPTE_ORIGIN = "COMPTE_ORIGIN";
    const CREDENTIAL_ADMIN = "ADMIN";
    const CREDENTIAL_DREV_ADMIN = 'teledeclaration_drev_admin';
    const CREDENTIAL_STALKER = 'stalker';
    const CREDENTIAL_TOURNEE = "tournee";
    const CREDENTIAL_CONTACT = "contacts";
    const CREDENTIAL_HABILITATION = "habilitation";
    const CREDENTIAL_DREV_REGION = "COMPTE_REGION";

    public function signInOrigin($login_or_compte) {

        $compte = $this->registerCompteByNamespace($login_or_compte, self::NAMESPACE_COMPTE_ORIGIN);

        $this->setAuthenticated(true);
        $this->signIn($login_or_compte);
    }

    public function signIn($login_or_compte) {
        $compte = $this->registerCompteByNamespace($login_or_compte, self::NAMESPACE_COMPTE);

        if ($compte && $compte->exist('droits')) {
            foreach ($compte->droits as $droit) {
                $droitTab = explode(":", $droit);
                $this->addCredentials(Roles::getRoles($droitTab[0]));
            }
        }
    }

    protected function registerCompteByNamespace($login_or_compte, $namespace) {
        if (is_object($login_or_compte) && $login_or_compte instanceof Compte) {
            $compte = $login_or_compte;
            $login = $compte->getLogin();
        } else {

            $compte = CompteClient::getInstance()->findByLogin($login_or_compte);
            $login = $login_or_compte;
        }

        if(!$compte) {
          $societe = SocieteClient::getInstance()->findByIdentifiantSociete($login_or_compte);
          if(!$societe){
             $this->signOut();
             return false;
          }
          $compte = $societe->getMasterCompte();
          $login = $compte->identifiant;
          if(!$compte){
              $this->signOut();
              return false;
          }
        }

        $this->setAttribute(self::SESSION_COMPTE_LOGIN, $login, $namespace);

        if ($compte->isNew()) {
            $this->setAttribute(self::SESSION_COMPTE_DOC, $compte->_id, $namespace);
        } else {
            $this->setAttribute(self::SESSION_COMPTE_DOC, $compte->_id, $namespace);
        }

        return $compte;
    }

    public function signOut() {
        $this->clearCredentials();
        $this->getAttributeHolder()->removeNamespace(self::NAMESPACE_COMPTE);
    }

    public function signOutOrigin() {
        $this->signOut();
        $this->setAuthenticated(false);
        $this->clearCredentials();
        $this->getAttributeHolder()->removeNamespace(self::NAMESPACE_COMPTE_ORIGIN);
    }

    public function getCompte() {

        return $this->getCompteByNamespace(self::NAMESPACE_COMPTE);
    }

    public function getCompteOrigin() {

        return $this->getCompteByNamespace(self::NAMESPACE_COMPTE_ORIGIN);
    }

    protected function getCompteByNamespace($namespace) {
        $id_or_doc = $this->getAttribute(self::SESSION_COMPTE_DOC, null, $namespace);

        if (!$id_or_doc) {
            return null;
        }

        if ($id_or_doc instanceof Compte) {

            return $id_or_doc;
        }

        if (preg_match('/^COMPTE-'.self::CREDENTIAL_ADMIN.'$/', $id_or_doc)) {
        	return $this->getAdminFictifCompte();
        }

        return CompteClient::getInstance()->find($id_or_doc);
    }

    public function getAdminFictifCompte() {
    	$compte = new Compte();

    	$compte->_id = "COMPTE-".self::CREDENTIAL_ADMIN;
    	$compte->identifiant = self::CREDENTIAL_ADMIN;
    	$compte->add('login', self::CREDENTIAL_ADMIN);

    	$compte->add("droits", sfConfig::get('app_auth_rights', array(self::CREDENTIAL_ADMIN)));

    	return $compte;
    }

    public function usurpationOn($login_or_compte, $url_back) {
        $this->signOut();
        $this->signIn($login_or_compte);
        $this->setAttribute(self::SESSION_USURPATION_URL_BACK, $url_back);
    }

    public function usurpationOff() {
        $this->signOut();
        $this->signIn($this->getCompteOrigin());

        $url_back = $this->getAttribute(self::SESSION_USURPATION_URL_BACK);
        $this->getAttributeHolder()->remove(self::SESSION_USURPATION_URL_BACK);

        return $url_back;
    }

    public function isUsurpationCompte() {

        return $this->getAttribute(self::SESSION_COMPTE_LOGIN, null, self::NAMESPACE_COMPTE) != $this->getAttribute(self::SESSION_COMPTE_LOGIN, null, self::NAMESPACE_COMPTE_ORIGIN);
    }

    public function isAdmin()
    {
       return $this->hasCredential(self::CREDENTIAL_ADMIN);
    }

    public function hasTeledeclaration() {
        return $this->isAuthenticated() && $this->getCompte() && !$this->isAdmin() && !$this->hasCredential(self::CREDENTIAL_HABILITATION) && !$this->hasDrevAdmin() && !$this->isStalker();
    }

    public function hasDrevAdmin() {
        return $this->hasCredential(self::CREDENTIAL_DREV_ADMIN) || $this->isAdmin();
    }

    public function isStalker() {
        return $this->hasCredential(self::CREDENTIAL_STALKER);
    }

    public function getTeledeclarationDrevRegion() {
      if($this->hasDrevAdmin() && $this->getCompte() && ($region = $this->getCompte()->getRegion()) && RegionConfiguration::getInstance()->getOdgRegions()){
        if(in_array($region, RegionConfiguration::getInstance()->getOdgRegions())){
                    return $region;
        }
      }
      return null;
    }

}
