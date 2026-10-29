<?php

abstract class BaseTransactionDeclarant extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'Transaction';
       $this->_tree_class_name = 'TransactionDeclarant';
    }

}
