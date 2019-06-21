<?php

abstract class BaseHabilitationDemande extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationDemande';
    }

}
