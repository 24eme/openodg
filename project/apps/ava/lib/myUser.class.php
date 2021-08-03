<?php

class myUser extends sfBasicSecurityUser
{

    const SESSION_LOGIN = "LOGIN";
    const SESSION_ETABLISSEMENT = "ETABLISSEMENT";
    const SESSION_COMPTE = "COMPTE";
    const NAMESPACE_AUTH = "AUTH";

    const CREDENTIAL_ADMIN = CompteClient::DROIT_ADMIN;
    const CREDENTIAL_TOURNEE = CompteClient::DROIT_TOURNEE;
    const CREDENTIAL_CONTACT = CompteClient::DROIT_CONTACT;
    const CREDENTIAL_HABILITATION = 'habilitation';

    protected $etablissement = null;
    protected $compte = null;

    public function signInOrigin($identifiant) {

        return $this->signIn($identifiant);
    }

    public function signIn($identifiant)
    {
        $this->setAttribute(self::SESSION_LOGIN, $identifiant, self::NAMESPACE_AUTH);
        $this->setAuthenticated(true);

        $etablissement = EtablissementClient::getInstance()->findByIdentifiant($identifiant);

        if($etablissement) {

            if ($etablissement->getCompte()->statut == CompteClient::STATUT_INACTIF) {
                throw new sfException("le compte ".$etablissement->getCompte()->_id." est inactif");
            }

            $this->signInEtablissement($etablissement);

            return;
        }

        $compte = CompteClient::getInstance()->findByIdentifiant($identifiant);

        if($compte) {

            if ($compte->statut == CompteClient::STATUT_INACTIF) {
                throw new sfException("le compte ".$compte->_id." est inactif");
            }

            $this->signInCompte($compte);

            return;
        }
    }

    public function signInCompte($compte) {
        $this->compte = null;
        $this->setAttribute(self::SESSION_COMPTE, $compte->_id, self::NAMESPACE_AUTH);

        foreach($compte->droits as $droit => $value) {
            $this->addCredential($droit);
        }
    }

    public function signInEtablissement($etablissement) {
        $this->etablissement = null;
        $this->setAttribute(self::SESSION_ETABLISSEMENT, $etablissement->_id, self::NAMESPACE_AUTH);
    }

    public function signOutEtablissement()
    {
        $this->setAttribute(self::SESSION_ETABLISSEMENT, null, self::NAMESPACE_AUTH);
        $this->etablissement = null;
    }

    public function signOut()
    {
        $this->setAuthenticated(false);
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
            $id = $this->getAttribute(self::SESSION_COMPTE, null, self::NAMESPACE_AUTH);

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

    public function getTeledeclarationDrevRegion()
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

        return $this->isAuthenticated() && $this->getCompte() && !$this->isAdmin() && !$this->hasCredential(self::CREDENTIAL_HABILITATION) && !$this->hasDrevAdmin() && !$this->isStalker();
    }
}
