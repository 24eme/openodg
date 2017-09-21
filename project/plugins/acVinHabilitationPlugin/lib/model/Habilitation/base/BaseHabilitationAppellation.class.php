<?php
/**
 * BaseHabilitationAppellation
 * 
 * Base model for HabilitationAppellation

 * @property string $libelle
 * @property HabilitationMention $mention

 * @method string getLibelle()
 * @method string setLibelle()
 * @method HabilitationMention getMention()
 * @method HabilitationMention setMention()
 
 */

abstract class BaseHabilitationAppellation extends _HabilitationDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationAppellation';
    }
                
}