<?php
/**
 * BaseDRaPDocuments
 *
 * Base model for DRaPDocuments


 */

 abstract class BaseDRaPDocuments extends acCouchdbDocumentTree {

     public function configureTree() {
         $this->_root_class_name = 'DRaP';
         $this->_tree_class_name = 'DRaPDocuments';
     }
 }
