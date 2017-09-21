<?php
/**
 * BaseHabilitationDeclaration
 * 
 * Base model for HabilitationDeclaration


 
 */

abstract class BaseHabilitationDeclaration extends _HabilitationDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationDeclaration';
    }
                
}