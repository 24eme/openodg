<?php

class
myUser extends sfBasicSecurityUser
{

    const SESSION_LOGIN = "LOGIN";
    const SESSION_ETABLISSEMENT = "ETABLISSEMENT";
    const SESSION_COMPTE = "COMPTE";
    const NAMESPACE_AUTH = "AUTH";

    const CREDENTIAL_ADMIN = 'admin';
    const CREDENTIAL_TOURNEE = 'tournee';
    const CREDENTIAL_CONTACT = 'contact';

    protected $etablissement = null;
    protected $compte = null;

    public function signInOrigin($login_or_compte) {
        $this->signIn($login_or_compte);
    }

    public function signIn($identifiant)
    {
        $this->setAttribute(self::SESSION_LOGIN, $identifiant, self::NAMESPACE_AUTH);
        $this->setAuthenticated(true);

        if($identifiant instanceof Compte) {
            $this->signInCompte($identifiant);

            return;
        }

        $compte = CompteClient::getInstance()->findByIdentifiant($identifiant);

        if($compte) {
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

            if ($id instanceof Compte) {

                return $id_or_doc;
            }

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

    public function hasTeledeclaration() {

        return false;
    }
}
