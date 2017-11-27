<?php
/**
 * BaseDRevAppellation
 * 
 * Base model for DRevAppellation

 * @property string $libelle
 * @property DRevMention $mention

 * @method string getLibelle()
 * @method string setLibelle()
 * @method DRevMention getMention()
 * @method DRevMention setMention()
 
 */

abstract class BaseDRevAppellation extends _DRevDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevAppellation';
    }
                
}