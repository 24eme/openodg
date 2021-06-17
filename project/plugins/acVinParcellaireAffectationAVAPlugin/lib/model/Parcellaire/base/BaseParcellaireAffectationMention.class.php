<?php
/**
 * BaseParcellaireAffectationMention
 *
 * Base model for ParcellaireMention

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()

 */

abstract class BaseParcellaireAffectationMention extends _ParcellaireAffectationDeclarationNoeud {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireAffectation';
       $this->_tree_class_name = 'ParcellaireAffectationMention';
    }

}