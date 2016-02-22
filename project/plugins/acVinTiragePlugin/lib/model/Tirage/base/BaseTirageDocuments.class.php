<?php
/**
 * BaseTirageDocuments
 * 
 * Base model for TirageDocuments


 
 */

abstract class BaseTirageDocuments extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Tirage';
       $this->_tree_class_name = 'TirageDocuments';
    }
                
}