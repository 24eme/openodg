<?php

class myUser extends sfBasicSecurityUser
{

    const SESSION_LOGIN = "LOGIN";
    const SESSION_COMPTE_DOC = "COMPTE_DOC_ID";
    const SESSION_COMPTE_LOGIN = "COMPTE_LOGIN";

    const SESSION_ETABLISSEMENT = "ETABLISSEMENT";
    const NAMESPACE_AUTH = "AUTH";
    const NAMESPACE_AUTH_ORIGIN = "AUTH_ORIGIN";
    const SESSION_USURPATION_URL_BACK = "USURPATION_URL_BACK";


    const CREDENTIAL_ADMIN = CompteClient::DROIT_ADMIN;
    const CREDENTIAL_ADMIN_ODG = "ADMIN_ODG";
    const CREDENTIAL_TOURNEE = CompteClient::DROIT_TOURNEE;
    const CREDENTIAL_CONTACT = CompteClient::DROIT_CONTACT;
    const CREDENTIAL_HABILITATION = 'habilitation';
    const CREDENTIAL_STALKER = 'stalker';

    protected $etablissement = null;
    protected $compte = null;

    public function signInOrigin($identifiant) {
        $compte = CompteClient::getInstance()->findByIdentifiant($identifiant);

        return $this->signIn($identifiant);
    }

    public function signIn($identifiant)
    {
        if (CompteClient::getInstance()->findByIdentifiant($identifiant) != null ) {
            $compte = $this->registerCompteByNamespace(CompteClient::getInstance()->findByIdentifiant($identifiant), self::NAMESPACE_AUTH_ORIGIN);

            $this->setAuthenticated(true);

            if ($compte->statut == CompteClient::STATUT_INACTIF) {
                throw new sfException("le compte ".$compte->_id." est inactif");
            }

            $this->signInCompte($compte);

            return;
    }

        if (EtablissementClient::getInstance()->findByIdentifiant($identifiant) != null) {

            $etablissement = $this->registerCompteByNamespace(EtablissementClient::getInstance()->findByIdentifiant($identifiant), self::NAMESPACE_AUTH_ORIGIN);

            $this->setAuthenticated(true);

            if ($etablissement->getCompte()->statut == CompteClient::STATUT_INACTIF) {
                throw new sfException("le compte ".$etablissement->getCompte()->_id." est inactif");
            }

            $this->signInEtablissement($etablissement);

            return;

        }
    }

    public function signInCompte($compte) {
        $this->compte = null;

        $compte = $this->registerCompteByNamespace($compte, self::NAMESPACE_AUTH);

        foreach($compte->droits as $droit => $value) {
            $this->addCredential($droit);
        }
    }

    protected function registerCompteByNamespace($login_or_compte, $namespace) {

        if (is_object($login_or_compte) && $login_or_compte instanceof Compte) {
            $compte = $login_or_compte;
            $login = $compte->getLogin();
        }

        if (!$compte){
            if (is_object($login_or_compte) && $login_or_compte instanceof Etablissement) {
                $etablissement = $login_or_compte;
                $compte = $etablissement->getCompte();
                $login = $compte->identifiant;

            $this->setAttribute(self::SESSION_COMPTE_LOGIN, $login, $namespace);
            $this->setAttribute(self::SESSION_COMPTE_DOC, $compte->_id, $namespace);

            return $etablissement;
            }

            $this->signOut();
            return false;
        }
        $this->setAttribute(self::SESSION_COMPTE_LOGIN, $login, $namespace);

        $this->setAttribute(self::SESSION_COMPTE_DOC, $compte->_id, $namespace);

        return $compte;
    }

    public function signInEtablissement($etablissement) {
        $this->etablissement = null;

        if (! (is_object($etablissement) && $etablissement instanceof Etablissement)) {
            $etablissement = $this->registerCompteByNamespace(EtablissementClient::getInstance()->find($etablissement), self::NAMESPACE_AUTH);
        }

        $etablissement = $this->registerCompteByNamespace($etablissement, self::NAMESPACE_AUTH);

        $this->setAttribute(self::SESSION_ETABLISSEMENT, $etablissement->_id, self::NAMESPACE_AUTH);
    }

    public function signOutEtablissement()
    {
        $this->setAttribute(self::SESSION_ETABLISSEMENT, null, self::NAMESPACE_AUTH);
        $this->etablissement = null;
    }

    public function signOutOrigin() {
        $this->signOut();
        $this->setAuthenticated(false);
        $this->clearCredentials();
        $this->getAttributeHolder()->removeNamespace(self::NAMESPACE_AUTH_ORIGIN);
    }

    public function signOut()
    {
        $this->clearCredentials();
        $this->getAttributeHolder()->removeNamespace(self::NAMESPACE_AUTH);
    }

    public function getEtablissement()
    {
        if(is_null($this->etablissement)) {
            $id = $this->getAttribute(self::SESSION_ETABLISSEMENT, null, self::NAMESPACE_AUTH);

            if(!$id) {

                return null;
            }

            $this->etablissement = EtablissementClient::getInstance()->find($id);
        }

        return $this->etablissement;
    }

    public function getCompte()
    {
        if(is_null($this->compte)) {

            $id = $this->getCompteByNamespace(self::NAMESPACE_AUTH);

            if(!$id) {
                return null;
            }

            $this->compte = CompteClient::getInstance()->find($id);
        }

        return $this->compte;
    }

    public function isAdmin()
    {
    	return $this->hasCredential(self::CREDENTIAL_ADMIN);
    }

    public function isAdminODG()
    {
       return $this->hasCredential(self::CREDENTIAL_ADMIN) || $this->hasCredential(self::CREDENTIAL_ADMIN_ODG);
    }

    public function hasFactureAdmin()
    {
       return $this->isAdminODG();
    }

    public function getRegion()
    {
        return null;
    }

    public function getTeledeclarationConditionnementRegion()
    {
        return null;
    }

    public function hasDrevAdmin() {
        return $this->isAdmin();
    }

    public function isStalker() {
        return $this->hasCredential(self::CREDENTIAL_STALKER);
    }

    public function hasTeledeclaration() {

        return $this->isAuthenticated() && $this->getCompte() && !$this->isAdmin() && !$this->hasHabilitation() && !$this->hasDrevAdmin() && !$this->isStalker();
    }

    public function hasHabilitation() {
        return $this->hasCredential(self::CREDENTIAL_HABILITATION)  || $this->isAdminODG();
    }

    public function isUsurpationCompte() {

        return $this->getAttribute(self::SESSION_COMPTE_LOGIN, null, self::NAMESPACE_AUTH) != $this->getAttribute(self::SESSION_COMPTE_LOGIN, null, self::NAMESPACE_AUTH_ORIGIN);
    }

    public function usurpationOn($identifiant, $url_back) {
        $this->signOut();
        $compte = CompteClient::getInstance()->findByIdentifiant($identifiant);
        $this->signInEtablissement($compte->getEtablissement());
        $this->setAttribute(self::SESSION_USURPATION_URL_BACK, $url_back);
    }

    public function usurpationOff() {
        $this->signOut();
        $this->signIn($this->getCompteOrigin()->getIdentifiant());

        $url_back = $this->getAttribute(self::SESSION_USURPATION_URL_BACK);
        $this->getAttributeHolder()->remove(self::SESSION_USURPATION_URL_BACK);

        return $url_back;
    }

    public function getCompteOrigin() {

        return $this->getCompteByNamespace(self::NAMESPACE_AUTH_ORIGIN);
    }

    protected function getCompteByNamespace($namespace) {
        $id_or_doc = $this->getAttribute(self::SESSION_COMPTE_DOC, null, $namespace);
        if (!$id_or_doc) {
            return null;
        }

        if ($id_or_doc instanceof Compte) {

            return $id_or_doc;
        }

        return CompteClient::getInstance()->find($id_or_doc);
    }

}
