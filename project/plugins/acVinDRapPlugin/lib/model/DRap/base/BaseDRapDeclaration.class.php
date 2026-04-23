<?php
/**
 * BaseDRapDeclaration
 *
 * Base model for DRapDeclaration



 */

abstract class BaseDRapDeclaration extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'DRap';
       $this->_tree_class_name = 'DRapDeclaration';
    }

}
