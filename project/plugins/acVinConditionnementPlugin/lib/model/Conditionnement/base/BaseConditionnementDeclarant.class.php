<?php

abstract class BaseConditionnementDeclarant extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'Conditionnement';
       $this->_tree_class_name = 'ConditionnementDeclarant';
    }

}
