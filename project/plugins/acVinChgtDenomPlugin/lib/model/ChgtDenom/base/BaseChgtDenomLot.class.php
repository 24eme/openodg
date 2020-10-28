<?php

abstract class BaseChgtDenomLot extends Lot {

    public function configureTree() {
       $this->_root_class_name = 'ChgtDenom';
       $this->_tree_class_name = 'ChgtDenomLot';
    }

}
