<?php
/**
 * BaseSV11MouvementFactures
 *
 * Base model for SV11MouvementFactures

 * @property integer $facture
 * @property integer $facturable

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()

 */

abstract class BaseSV11MouvementFactures extends MouvementFactures {

    public function configureTree() {
       $this->_root_class_name = 'SV11';
       $this->_tree_class_name = 'SV11MouvementFactures';
    }

}
