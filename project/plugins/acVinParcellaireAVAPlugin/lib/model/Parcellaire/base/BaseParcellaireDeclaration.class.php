<?php
/**
 * BaseParcellaireDeclaration
 * 
 * Base model for ParcellaireDeclaration


 
 */

abstract class BaseParcellaireDeclaration extends _ParcellaireDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Parcellaire';
       $this->_tree_class_name = 'ParcellaireDeclaration';
    }
                
}