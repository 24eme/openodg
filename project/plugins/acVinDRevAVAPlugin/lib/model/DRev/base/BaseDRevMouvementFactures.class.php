<?php
/**
 * BaseDRevMouvementFactures
 *
 * Base model for BaseDRevMouvementFactures

 * @property integer $facture
 * @property integer $facturable

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()

 */

abstract class BaseDRevMouvementFactures extends MouvementFactures {

    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevMouvementFactures';
    }

}
