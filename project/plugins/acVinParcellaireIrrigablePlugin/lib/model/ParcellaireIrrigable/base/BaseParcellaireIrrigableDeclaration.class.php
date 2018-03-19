<?php
/**
 * BaseParcellaireIrrigableDeclaration
 * 
 * Base model for ParcellaireIrrigableDeclaration


 
 */

abstract class BaseParcellaireIrrigableDeclaration extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'ParcellaireIrrigable';
       $this->_tree_class_name = 'ParcellaireIrrigableDeclaration';
    }
                
}