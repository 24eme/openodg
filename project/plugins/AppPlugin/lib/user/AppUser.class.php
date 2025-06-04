<?php

class AppUser extends sfBasicSecurityUser {

    const SESSION_COMPTE_LOGIN = "COMPTE_LOGIN";
    const SESSION_COMPTE_DOC = "COMPTE_DOC_ID";
    const SESSION_USURPATION_URL_BACK = "USURPATION_URL_BACK";
    const NAMESPACE_COMPTE = "COMPTE";
    const NAMESPACE_COMPTE_ORIGIN = "COMPTE_ORIGIN";
    const CREDENTIAL_ADMIN = "ADMIN";
    const CREDENTIAL_ADMIN_ODG = "ADMIN_ODG";
    const CREDENTIAL_DREV_ADMIN = 'teledeclaration_drev_admin';
    const CREDENTIAL_CONDITIONNEMENT_ADMIN = 'teledeclaration_conditionnement_admin';
    const CREDENTIAL_PMC_ADMIN = 'teledeclaration_pmc_admin';
    const CREDENTIAL_CHGTDENOM_ADMIN = 'teledeclaration_chgtDenom_admin';
    const CREDENTIAL_TRANSACTION_ADMIN = 'teledeclaration_transaction_admin';
    const CREDENTIAL_STALKER = 'stalker';
    const CREDENTIAL_TOURNEE = "tournee";
    const CREDENTIAL_CONTACT = "contacts";
    const CREDENTIAL_HABILITATION = 'habilitation';
    const CREDENTIAL_OI = 'OI';
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
          if(!$societe || $societe->isSuspendu()){
              $this->signOut();
              return false;
              //throw new sfException("Le compte est nul : ".$login_or_compte);
          }
          $compte = $societe->getMasterCompte();
          $login = $compte->identifiant;
          if(!$compte){
              $this->signOut();
              return false;
          }
        }

        if ($compte->statut === EtablissementClient::STATUT_SUSPENDU) {
            $this->signOut();
            return false;
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

    private $cache_compte = null;
    public function getCompte() {
        if (is_null($this->cache_compte)) {
            $this->cache_compte = $this->getCompteByNamespace(self::NAMESPACE_COMPTE);
        }
        return $this->cache_compte;
    }

    public function getEtablissement() {
        $etablissement = null;

        $compte = $this->getCompte();
        $societe = $compte->getSociete() ;
        if ($societe) {
            $etablissement = $societe->getEtablissementPrincipal();
        }
        if (!$etablissement) {
            $etablissement = $compte->getEtablissement();
        }

        return $etablissement;
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

    public function isAdminODG()
    {
       return $this->hasCredential(self::CREDENTIAL_ADMIN) || $this->hasCredential(self::CREDENTIAL_ADMIN_ODG);
    }

    public function hasContact() {
        return $this->hasCredential(self::CREDENTIAL_CONTACT) || $this->isAdminODG();
    }

    public function hasHabilitation() {
        return $this->hasCredential(AppUser::CREDENTIAL_HABILITATION)  || $this->isAdminODG();
    }

    public function hasFactureAdmin()
    {
       return $this->hasCredential(self::CREDENTIAL_ADMIN) || $this->hasCredential(self::CREDENTIAL_ADMIN_ODG);
    }

    public function hasTeledeclaration() {
        return $this->isAuthenticated() && $this->getCompte() && !$this->isAdmin() && !$this->hasCredential(self::CREDENTIAL_HABILITATION) && !$this->hasDrevAdmin() && !$this->isStalker();
    }

    public function hasDrevAdmin() {
        return $this->hasCredential(self::CREDENTIAL_DREV_ADMIN) || $this->isAdminODG();
    }

    public function hasChgtDenomAdmin() {
        return $this->hasCredential(self::CREDENTIAL_CHGTDENOM_ADMIN) || $this->isAdminODG();
    }

    public function hasConditionnementAdmin() {
        return $this->hasCredential(self::CREDENTIAL_CONDITIONNEMENT_ADMIN) || $this->isAdminODG();
    }

    public function hasPMCAdmin() {
        return $this->hasCredential(self::CREDENTIAL_PMC_ADMIN) || $this->isAdminODG();
    }

    public function hasTransactionAdmin() {
        return $this->hasCredential(self::CREDENTIAL_TRANSACTION_ADMIN) || $this->isAdminODG();
    }

    public function isStalker() {
        return $this->hasCredential(self::CREDENTIAL_STALKER) || $this->isAdmin() || $this->isAdminODG();
    }


    public function getRegion() {
      if(RegionConfiguration::getInstance()->getOdgRegions() && $this->hasDrevAdmin() && $this->getCompte() && ($region = $this->getCompte()->getRegion())){
        if(in_array($region, RegionConfiguration::getInstance()->getOdgRegions())){
                    return $region;
        }
      }

      return sfConfig::get('app_region', null);
    }

    public function getTeledeclarationConditionnementRegion() {
      $condConf = ConditionnementConfiguration::getInstance();
      if($this->hasConditionnementAdmin() && $this->getCompte() && ($region = $this->getCompte()->getRegion()) && $condConf->getOdgRegions()){
        if(in_array($region, $condConf->getOdgRegions())){
                    return $region;
        }
      }
      return null;
    }

    public function getTeledeclarationPMCRegion() {
      $condConf = PMCConfiguration::getInstance();
      if($this->hasPMCAdmin() && $this->getCompte() && ($region = $this->getCompte()->getRegion()) && $condConf->getOdgRegions()){
        if(in_array($region, $condConf->getOdgRegions())){
                    return $region;
        }
      }
      return null;
    }

    public function getTeledeclarationTransactionRegion() {
      $transConf = TransactionConfiguration::getInstance();
      if($this->hasTransactionAdmin() && $this->getCompte() && ($region = $this->getCompte()->getRegion()) && $transConf->getOdgRegions()){
        if(in_array($region, $transConf->getOdgRegions())){
                    return $region;
        }
      }
      return null;
    }

}
