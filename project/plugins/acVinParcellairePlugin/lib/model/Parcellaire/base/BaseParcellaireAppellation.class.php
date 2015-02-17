<?php
/**
 * BaseParcellaireAppellation
 * 
 * Base model for ParcellaireAppellation

 * @property string $libelle
 * @property ParcellaireMention $mention

 * @method string getLibelle()
 * @method string setLibelle()
 * @method ParcellaireMention getMention()
 * @method ParcellaireMention setMention()
 
 */

abstract class BaseParcellaireAppellation extends _ParcellaireDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Parcellaire';
       $this->_tree_class_name = 'ParcellaireAppellation';
    }
                
}