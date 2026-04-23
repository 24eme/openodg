<?php
/**
 * BaseDRapDocuments
 *
 * Base model for DRapDocuments


 */

 abstract class BaseDRapDocuments extends acCouchdbDocumentTree {

     public function configureTree() {
         $this->_root_class_name = 'DRap';
         $this->_tree_class_name = 'DRapDocuments';
     }
 }
