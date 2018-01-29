<?php
/**
 * BaseParcellaireGenre
 * 
 * Base model for ParcellaireGenre

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseParcellaireGenre extends _ParcellaireDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Parcellaire';
       $this->_tree_class_name = 'ParcellaireGenre';
    }
                
}