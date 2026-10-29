<?php
/**
 * BaseDRaPDeclaration
 *
 * Base model for DRaPDeclaration



 */

abstract class BaseDRaPDeclaration extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'DRaP';
       $this->_tree_class_name = 'DRaPDeclaration';
    }

}
