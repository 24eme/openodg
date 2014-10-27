<?php

class myUser extends sfBasicSecurityUser
{

    const SESSION_LOGIN = "LOGIN";
    const SESSION_ETABLISSEMENT = "ETABLISSEMENT";
    const NAMESPACE_AUTH = "AUTH";

    protected $etablissement = null;

    public function signIn($identifiant) 
    {
        $this->setAttribute(self::SESSION_LOGIN, $identifiant, self::NAMESPACE_AUTH);
        $this->setAuthenticated(true);

        $etablissement = EtablissementClient::getInstance()->findByIdentifiant($identifiant);
        if(!$etablissement) {

            return;
        }

        $this->setAttribute(self::SESSION_ETABLISSEMENT, $etablissement->_id, self::NAMESPACE_AUTH);
        
        foreach($etablissement->droits as $droit) {
            $roles = Roles::getRoles($droit);
            $this->addCredentials($droit);
        }

         $this->addCredentials('drev');
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
            $this->etablissement = EtablissementClient::getInstance()->find($this->getAttribute(self::SESSION_ETABLISSEMENT, null, self::NAMESPACE_AUTH));
        }

        return $this->etablissement;
    }
    
    public function isAdmin()
    {
    	return false;
    }
}
