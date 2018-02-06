<?php
/**
 * BaseRegistreVCIDeclaration
 * 
 * Base model for RegistreVCIDeclaration


 
 */

abstract class BaseRegistreVCIDeclaration extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'RegistreVCI';
       $this->_tree_class_name = 'RegistreVCIDeclaration';
    }
                
}