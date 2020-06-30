<?php

class TmpParcellesView extends acCouchdbView
{
    public static function getInstance() {
        
        return acCouchdbManager::getView('tmp', 'parcelles', 'Parcellaire');
    }

    public function findByIdu($idu) {  
            return acCouchdbManager::getClient()
                    ->startkey(array($idu))
                    ->endkey(array($idu, array()))
                    ->getView($this->design, $this->view)->rows;            
    }
    
}  
