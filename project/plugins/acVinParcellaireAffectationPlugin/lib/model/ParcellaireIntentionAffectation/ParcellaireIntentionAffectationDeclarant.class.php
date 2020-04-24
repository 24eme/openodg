<?php
/**
 * Model for ParcellaireIntentionAffectationDeclarant
 *
 */

class ParcellaireIntentionAffectationDeclarant extends ParcellaireAffectationDeclarant {
    public function configureTree() {
        $this->_root_class_name = 'ParcellaireIntentionAffectation';
        $this->_tree_class_name = 'ParcellaireIntentionAffectationDeclarant';
    }
}
