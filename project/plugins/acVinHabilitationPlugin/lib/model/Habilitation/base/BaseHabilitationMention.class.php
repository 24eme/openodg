<?php
/**
 * BaseHabilitationMention
 * 
 * Base model for HabilitationMention

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseHabilitationMention extends _HabilitationDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationMention';
    }
                
}