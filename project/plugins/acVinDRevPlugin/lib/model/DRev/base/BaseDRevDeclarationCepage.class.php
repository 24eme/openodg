<?php
/**
 * BaseDRevDeclaration
 *
 * Base model for DRevDeclaration



 */

abstract class BaseDRevDeclarationCepage extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevDeclarationCepage';
    }

}
