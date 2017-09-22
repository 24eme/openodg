<?php
/**
 * BaseHabilitationLieu
 * 
 * Base model for HabilitationLieu

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseHabilitationLieu extends _HabilitationDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationLieu';
    }
                
}