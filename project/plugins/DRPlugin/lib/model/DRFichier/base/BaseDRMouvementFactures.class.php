<?php
/**
 * BaseDRMouvementFactures
 *
 * Base model for DRMouvementFactures

 * @property integer $facture
 * @property integer $facturable

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()

 */

abstract class BaseDRMouvementFactures extends MouvementFactures {

    public function configureTree() {
       $this->_root_class_name = 'DR';
       $this->_tree_class_name = 'DRMouvementFactures';
    }

}
