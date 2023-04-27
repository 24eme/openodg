<?php
/**
 * BaseConditionnementMouvementFactures
 *
 * Base model for ConditionnementMouvementFactures

 * @property integer $facture
 * @property integer $facturable

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()

 */

abstract class BaseConditionnementMouvementFactures extends MouvementFactures {

    public function configureTree() {
       $this->_root_class_name = 'Conditionnement';
       $this->_tree_class_name = 'ConditionnementMouvementFactures';
    }

}

