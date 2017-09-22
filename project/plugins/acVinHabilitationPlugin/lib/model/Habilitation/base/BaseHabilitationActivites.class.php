<?php
/**
 * BaseHabilitationActivites
 * 
 * Base model for HabilitationActivites


 
 */

abstract class BaseHabilitationActivites extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationActivites';
    }
                
}