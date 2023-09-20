<?php
/**
 * Model for ParcellaireIntentionAffectationProduit
 *
 */

class ParcellaireIntentionAffectationProduit extends ParcellaireAffectationProduit {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireItentionAffectation';
       $this->_tree_class_name = 'ParcellaireIntentionAffectationProduit';
    }

}