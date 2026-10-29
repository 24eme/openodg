<?php

abstract class BasePMCLot extends Lot {

    public function configureTree() {
       $this->_root_class_name = 'PMC';
       $this->_tree_class_name = 'PMCLot';
    }

}
