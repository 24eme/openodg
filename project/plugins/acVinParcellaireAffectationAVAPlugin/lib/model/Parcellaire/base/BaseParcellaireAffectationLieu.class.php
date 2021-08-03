<?php
/**
 * BaseParcellaireAffectationLieu
 *
 * Base model for ParcellaireLieu

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()

 */

abstract class BaseParcellaireAffectationLieu extends _ParcellaireAffectationDeclarationNoeud {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireAffectation';
       $this->_tree_class_name = 'ParcellaireAffectationLieu';
    }

}