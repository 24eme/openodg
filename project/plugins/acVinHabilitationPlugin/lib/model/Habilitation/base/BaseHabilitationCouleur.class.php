<?php
/**
 * BaseHabilitationCouleur
 * 
 * Base model for HabilitationCouleur

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseHabilitationCouleur extends _HabilitationDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationCouleur';
    }
                
}