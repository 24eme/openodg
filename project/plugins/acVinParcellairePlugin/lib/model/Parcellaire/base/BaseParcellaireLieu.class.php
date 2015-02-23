<?php
/**
 * BaseParcellaireLieu
 * 
 * Base model for ParcellaireLieu

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseParcellaireLieu extends _ParcellaireDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Parcellaire';
       $this->_tree_class_name = 'ParcellaireLieu';
    }
                
}