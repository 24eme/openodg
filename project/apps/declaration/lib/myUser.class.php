<?php

class myUser extends sfBasicSecurityUser
{

    const SESSION_COMPTE = "COMPTE";
    const NAMESPACE_COMPTE = "COMPTE";

    public function signIn(Etablissement $etablissement) 
    {
        $this->setAttribute(self::SESSION_COMPTE, $etablissement->_id, self::NAMESPACE_COMPTE);

        foreach($etablissement->droits as $droit) {
            //$roles = Roles::getRoles($droit);
            $this->addCredentials($droit);
        }

        $this->setAuthenticated(true);
    }

    public function signOut() 
    {
        $this->setAuthenticated(false);
        $this->clearCredentials();
        $this->getAttributeHolder()->removeNamespace(self::NAMESPACE_COMPTE);
    }

    public function getEtablissement() 
    {
        return EtablissementClient::getInstance()->find($this->getAttribute(self::SESSION_COMPTE, null, self::NAMESPACE_COMPTE));
    }
}
