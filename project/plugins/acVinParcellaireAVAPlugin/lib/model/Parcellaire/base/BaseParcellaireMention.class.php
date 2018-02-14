<?php
/**
 * BaseParcellaireMention
 * 
 * Base model for ParcellaireMention

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseParcellaireMention extends _ParcellaireDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Parcellaire';
       $this->_tree_class_name = 'ParcellaireMention';
    }
                
}