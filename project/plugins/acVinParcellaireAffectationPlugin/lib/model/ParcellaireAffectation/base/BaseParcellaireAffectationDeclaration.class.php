<?php
/**
 * BaseParcellaireAffectationDeclaration
 * 
 * Base model for ParcellaireAffectationDeclaration


 
 */

abstract class BaseParcellaireAffectationDeclaration extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'ParcellaireAffectation';
       $this->_tree_class_name = 'ParcellaireAffectationDeclaration';
    }
                
}