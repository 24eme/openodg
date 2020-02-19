<?php
/**
 * BaseParcellaireIntentionAffectationDeclaration
 * 
 * Base model for ParcellaireIntentionAffectationDeclaration


 
 */

abstract class BaseParcellaireIntentionAffectationDeclaration extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'ParcellaireIntentionAffectation';
       $this->_tree_class_name = 'ParcellaireIntentionAffectationDeclaration';
    }
                
}