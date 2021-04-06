<?php

abstract class BaseConditionnementMouvementLots extends MouvementLots {

    public function configureTree() {
       $this->_root_class_name = 'Conditionnement';
       $this->_tree_class_name = 'ConditionnementMouvementLots';
    }

}
