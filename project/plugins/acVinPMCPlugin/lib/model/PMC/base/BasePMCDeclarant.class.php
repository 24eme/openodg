<?php

abstract class BasePMCDeclarant extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'PMC';
       $this->_tree_class_name = 'PMCDeclarant';
    }

}
