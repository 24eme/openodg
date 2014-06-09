<?php

abstract class BaseDRevGenre extends _DRevDeclarationNoeud {
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevGenre';
    }
}
