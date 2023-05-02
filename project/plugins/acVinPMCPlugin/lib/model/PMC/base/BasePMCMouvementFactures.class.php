<?php
/**
 * BasePMCMouvementFactures
 *
 * Base model for PMCMouvementFactures

 * @property integer $facture
 * @property integer $facturable

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()

 */

abstract class BasePMCMouvementFactures extends MouvementFactures {

    public function configureTree() {
       $this->_root_class_name = 'PMC';
       $this->_tree_class_name = 'PMCMouvementFactures';
    }

}

