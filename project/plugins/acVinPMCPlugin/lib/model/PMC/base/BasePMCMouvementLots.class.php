<?php

abstract class BasePMCMouvementLots extends MouvementLots {

    public function configureTree() {
       $this->_root_class_name = 'PMC';
       $this->_tree_class_name = 'PMCMouvementLots';
    }

}
