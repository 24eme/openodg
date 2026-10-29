<?php

abstract class BaseCourrierDeclarant extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'Courrier';
       $this->_tree_class_name = 'CourrierDeclarant';
    }

}
