<?php
/**
 * BaseParcellaireCouleur
 * 
 * Base model for ParcellaireCouleur

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseParcellaireCouleur extends _ParcellaireDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Parcellaire';
       $this->_tree_class_name = 'ParcellaireCouleur';
    }
                
}