<?php
/**
 * BaseDRevDocuments
 * 
 * Base model for DRevDocuments


 
 */

abstract class BaseDRevDocuments extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevDocuments';
    }
                
}