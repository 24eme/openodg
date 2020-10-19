<?php

abstract class BaseChgtDenomMouvementLots extends MouvementLots {

    public function configureTree() {
       $this->_root_class_name = 'ChgtDenom';
       $this->_tree_class_name = 'ChgtDenomMouvementLots';
    }

}
