<?php

abstract class BaseCourrierMouvementLots extends MouvementLots {

    public function configureTree() {
       $this->_root_class_name = 'Courrier';
       $this->_tree_class_name = 'CourrierMouvementLots';
    }

}
