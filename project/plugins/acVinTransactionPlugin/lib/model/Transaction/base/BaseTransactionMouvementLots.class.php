<?php

abstract class BaseTransactionMouvementLots extends MouvementLots {

    public function configureTree() {
       $this->_root_class_name = 'Transaction';
       $this->_tree_class_name = 'TransactionMouvementLots';
    }

}
