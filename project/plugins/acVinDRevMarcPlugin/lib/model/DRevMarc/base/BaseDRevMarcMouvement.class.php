<?php
/**
 * BaseDRevMarcMouvement
 * 
 * Base model for DRevMarcMouvement

 * @property integer $facture
 * @property integer $facturable

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()
 
 */

abstract class BaseDRevMarcMouvement extends Mouvement {
                
    public function configureTree() {
       $this->_root_class_name = 'DRevMarc';
       $this->_tree_class_name = 'DRevMarcMouvement';
    }
                
}