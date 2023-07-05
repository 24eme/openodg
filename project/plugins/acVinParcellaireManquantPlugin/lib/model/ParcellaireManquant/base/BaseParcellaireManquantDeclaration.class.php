<?php
/**
 * BaseParcellaireManquantDeclaration
 *
 * Base model for ParcellaireManquantDeclaration



 */

abstract class BaseParcellaireManquantDeclaration extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireManquant';
       $this->_tree_class_name = 'ParcellaireManquantDeclaration';
    }

}
