<?php
class avaComponents extends sfComponents 
{
    
    public function executeHistory(sfWebRequest $request) 
    {
        $this->etablissement = $this->getUser()->getEtablissement();
        $this->history = array();

        $docs = array();
        $docs['DRev'] = DRevClient::getInstance()->getHistory($this->etablissement->identifiant);
        $docs['DRevMarc'] = DRevMarcClient::getInstance()->getHistory($this->etablissement->identifiant);
        $docs['parcellaire'] = ParcellaireClient::getInstance()->getHistory($this->etablissement->identifiant);
        $docs['parcellairesCremant'] = ParcellaireClient::getInstance()->getHistory($this->etablissement->identifiant, true);
        $docs['tirage'] = tirageClient::getInstance()->getHistory($this->etablissement->identifiant);

        foreach ($docs as $key => $historyclient) {
            foreach ($historyclient as $history) {
                $this->history[$history->validation.$history->_id] = $history;
            }
        }

        krsort($this->history);
    }
    
}
