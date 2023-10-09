<?php

abstract class BaseCourrierLot extends Lot {

    public function configureTree() {
       $this->_root_class_name = 'Courrier';
       $this->_tree_class_name = 'CourrierLot';
    }

}
