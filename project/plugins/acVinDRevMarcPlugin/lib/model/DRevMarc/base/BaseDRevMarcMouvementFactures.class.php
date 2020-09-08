<?php
/**
 * BaseDRevMarcMouvementFactures
 *
 * Base model for DRevMarcMouvementFactures

 * @property integer $facture
 * @property integer $facturable

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()

 */

abstract class BaseDRevMarcMouvementFactures extends MouvementFactures {

    public function configureTree() {
       $this->_root_class_name = 'DRevMarc';
       $this->_tree_class_name = 'DRevMarcMouvementFactures';
    }

}
