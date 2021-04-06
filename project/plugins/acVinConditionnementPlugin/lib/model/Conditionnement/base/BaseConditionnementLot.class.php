<?php

abstract class BaseConditionnementLot extends Lot {

    public function configureTree() {
       $this->_root_class_name = 'Conditionnement';
       $this->_tree_class_name = 'ConditionnementLot';
    }

}
