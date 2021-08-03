<?php
/**
 * BaseParcellaireAffectationGenre
 *
 * Base model for ParcellaireGenre

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()

 */

abstract class BaseParcellaireAffectationGenre extends _ParcellaireAffectationDeclarationNoeud {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireAffectation';
       $this->_tree_class_name = 'ParcellaireAffectationGenre';
    }

}