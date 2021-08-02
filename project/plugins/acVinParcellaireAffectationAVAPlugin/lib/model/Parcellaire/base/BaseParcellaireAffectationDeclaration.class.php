<?php
/**
 * BaseParcellaireAffectationDeclaration
 *
 * Base model for ParcellaireDeclaration



 */

abstract class BaseParcellaireAffectationDeclaration extends _ParcellaireAffectationDeclarationNoeud {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireAffectation';
       $this->_tree_class_name = 'ParcellaireAffectationDeclaration';
    }

}