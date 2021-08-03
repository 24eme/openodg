<?php
/**
 * BaseParcellaireAffectationAppellation
 *
 * Base model for ParcellaireAppellation

 * @property string $libelle
 * @property ParcellaireMention $mention

 * @method string getLibelle()
 * @method string setLibelle()
 * @method ParcellaireMention getMention()
 * @method ParcellaireMention setMention()

 */

abstract class BaseParcellaireAffectationAppellation extends _ParcellaireAffectationDeclarationNoeud {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireAffectation';
       $this->_tree_class_name = 'ParcellaireAffectationAppellation';
    }

}