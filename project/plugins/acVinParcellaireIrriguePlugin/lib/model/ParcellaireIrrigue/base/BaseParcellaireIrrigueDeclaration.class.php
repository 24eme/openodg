<?php
/**
 * BaseParcellaireIrrigueDeclaration
 * 
 * Base model for ParcellaireIrrigueDeclaration


 
 */

abstract class BaseParcellaireIrrigueDeclaration extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'ParcellaireIrrigue';
       $this->_tree_class_name = 'ParcellaireIrrigueDeclaration';
    }
                
}