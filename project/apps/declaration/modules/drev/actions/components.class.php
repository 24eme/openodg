<?php
class drevComponents extends sfComponents 
{
    
    public function executeMonEspace(sfWebRequest $request) 
    {
        $this->etablissement = $this->getUser()->getEtablissement();
        $this->drev = DRevClient::getInstance()->find('DREV-'.$this->etablissement->identifiant.'-2013-2014');
        $this->drevmarc = DRevMarcClient::getInstance()->find('DREVMARC-'.$this->etablissement->identifiant.'-2013-2014');
    }
    
}
