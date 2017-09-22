<?php
/**
 * BaseHabilitationGenre
 * 
 * Base model for HabilitationGenre

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseHabilitationGenre extends _HabilitationDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationGenre';
    }
                
}