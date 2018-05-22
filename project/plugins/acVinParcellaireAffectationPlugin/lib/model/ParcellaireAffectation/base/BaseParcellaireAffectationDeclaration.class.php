<?php
/**
 * BaseParcellaireDeclaration
 *
 * Base model for ParcellaireDeclaration



 */

abstract class BaseParcellaireAffectationDeclaration extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireAffectation';
       $this->_tree_class_name = 'ParcellaireAffectationDeclaration';
    }

}
