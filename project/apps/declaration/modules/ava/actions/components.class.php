<?php
class avaComponents extends sfComponents 
{
    
    public function executeHistory(sfWebRequest $request) 
    {
        $this->etablissement = $this->getUser()->getEtablissement();
        $this->history = array();
        $drevsHistory = DRevClient::getInstance()->getHistory($this->etablissement->identifiant);
        $drevmarcsHistory = DRevMarcClient::getInstance()->getHistory($this->etablissement->identifiant);
        foreach ($drevsHistory as $drevHistory) {
        	$this->history['DREV'.$drevHistory->campagne] = $drevHistory;
        }
    	foreach ($drevmarcsHistory as $drevmarcHistory) {
        	$this->history['DREVM'.$drevmarcHistory->campagne] = $drevmarcHistory;
        }
        ksort($this->history);
    }
    
}
