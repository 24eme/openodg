<?php
/**
 * Model for ParcellaireIntentionAffectationDeclaration
 *
 */

class ParcellaireIntentionAffectationDeclaration extends ParcellaireAffectationDeclaration {

    public function configureTree() {
        $this->_root_class_name = 'ParcellaireIntentionAffectation';
        $this->_tree_class_name = 'ParcellaireIntentionAffectationDeclaration';
    }
}
