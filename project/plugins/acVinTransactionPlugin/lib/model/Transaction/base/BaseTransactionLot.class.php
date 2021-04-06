<?php

abstract class BaseTransactionLot extends Lot {

    public function configureTree() {
       $this->_root_class_name = 'Transaction';
       $this->_tree_class_name = 'TransactionLot';
    }

}
