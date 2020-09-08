<?php
/**
 * BaseDRevMouvementLots
 *
 * Base model for DRevMouvementFactures
 *
 */

abstract class BaseDRevMouvementLots extends MouvementLots {

    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevMouvementLots';
    }

}
