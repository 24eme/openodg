<?php
class drevComponents extends sfComponents 
{
    
    public function executeMonEspace(sfWebRequest $request) 
    {
        $this->etablissement = $this->getUser()->getEtablissement();
        $campagne = ConfigurationClient::getInstance()->getCampagneManager()->getCurrent();
        $this->drev = DRevClient::getInstance()->find('DREV-'.$this->etablissement->identifiant.'-'.$campagne);
        $this->drevmarc = DRevMarcClient::getInstance()->find('DREVMARC-'.$this->etablissement->identifiant.'-'.$campagne);
        $this->drevsHistory = DRevClient::getInstance()->getHistory();
    }
    
}
