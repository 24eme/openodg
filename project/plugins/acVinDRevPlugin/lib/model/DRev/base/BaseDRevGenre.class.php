<?php
/**
 * BaseDRevGenre
 * 
 * Base model for DRevGenre

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseDRevGenre extends _DRevDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevGenre';
    }
                
}