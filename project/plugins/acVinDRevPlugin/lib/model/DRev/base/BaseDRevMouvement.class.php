<?php
/**
 * BaseDRevMouvement
 * 
 * Base model for DRevMouvement

 * @property integer $facture
 * @property integer $facturable

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()
 
 */

abstract class BaseDRevMouvement extends Mouvement {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevMouvement';
    }
                
}