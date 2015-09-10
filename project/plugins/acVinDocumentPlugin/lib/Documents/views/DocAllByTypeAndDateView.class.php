<?php

class DocAllByTypeAndDateView extends acCouchdbView
{
    
    public static function getInstance() {

        return acCouchdbManager::getView('doc', 'allByTypeAndDate');
    }
    
    public function allByTypeAndDate($type,$date) {        
        return $this->client
            ->startkey(array($type,$date))
            ->endkey(array($type,$date))
            ->reduce(false)
            ->getView($this->design, $this->view)->rows;
    }    
}  
