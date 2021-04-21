<?php
/**
 * BaseSV12MouvementFactures
 *
 * Base model for SV12MouvementFactures

 * @property integer $facture
 * @property integer $facturable

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()

 */

abstract class BaseSV12MouvementFactures extends MouvementFactures {

    public function configureTree() {
       $this->_root_class_name = 'SV12';
       $this->_tree_class_name = 'SV12MouvementFactures';
    }

}
