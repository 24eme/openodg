<?php
/**
 * BaseDRevMouvementLots
 *
 * Base model for DegustationMouvementLots
 *
 */

abstract class BaseDegustationMouvementLots extends MouvementLots {

    public function configureTree() {
       $this->_root_class_name = 'Degustation';
       $this->_tree_class_name = 'DegustationMouvementLots';
    }

}
