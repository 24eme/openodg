<?php
/**
 * BaseDRevMouvementFactures
 *
 * Base model for DRevMouvementFactures

 * @property integer $facture
 * @property integer $facturable

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()

 */

abstract class BaseHabilitationMouvementFactures extends MouvementFactures {

    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationMouvementFactures';
    }

}
