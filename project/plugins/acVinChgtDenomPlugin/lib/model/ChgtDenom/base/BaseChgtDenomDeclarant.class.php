<?php

abstract class BaseChgtDenomDeclarant extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'ChgtDenom';
       $this->_tree_class_name = 'ChgtDenomDeclarant';
    }

}
