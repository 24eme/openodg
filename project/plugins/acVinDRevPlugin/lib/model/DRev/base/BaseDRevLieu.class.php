<?php
/**
 * BaseDRevLieu
 * 
 * Base model for DRevLieu

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseDRevLieu extends _DRevDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevLieu';
    }
                
}