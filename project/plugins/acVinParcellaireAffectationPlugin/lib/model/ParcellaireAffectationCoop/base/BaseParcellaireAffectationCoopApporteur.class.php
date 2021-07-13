<?php

abstract class BaseParcellaireAffectationCoopApporteur extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireAffectationCoop';
       $this->_tree_class_name = 'ParcellaireAffectationCoopApporteur';
    }

}