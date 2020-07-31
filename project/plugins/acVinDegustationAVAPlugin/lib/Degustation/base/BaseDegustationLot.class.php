<?php
/**
 * BaseDegustationLot
 * 
 * Base model for DegustationLot


 
 */

abstract class BaseDegustationLot extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Degustation';
       $this->_tree_class_name = 'DegustationLot';
    }
                
}