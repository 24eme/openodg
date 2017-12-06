<?php

abstract class BaseDRevDeclarant extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevDeclarant';
    }

}
