<?php 
/**
 * BaseParcellaireIrrigableDocuments
 * 
 * Base model for ParcellaireIrrigableDocuments
 
 
 */

 abstract class BaseParcellaireIrrigableDocuments extends acCouchdbDocumentTree {

     public function configureTree() {
         $this->_root_class_name = 'ParcellaireIrrigable';
         $this->_tree_class_name = 'ParcellaireIrrigableDocuments';
     }
 }