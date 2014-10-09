<?php
class drevmarcComponents extends sfComponents 
{
    
    public function executeMonEspace(sfWebRequest $request) 
    {
        $this->etablissement = $this->getUser()->getEtablissement();
        $campagne = ConfigurationClient::getInstance()->getCampagneManager()->getCurrent();
        $this->drevmarc = DRevMarcClient::getInstance()->find('DREVMARC-'.$this->etablissement->identifiant.'-'.$campagne);
        $this->drevmarcsHistory = DRevMarcClient::getInstance()->getHistory($this->etablissement->identifiant);
    }
    
}