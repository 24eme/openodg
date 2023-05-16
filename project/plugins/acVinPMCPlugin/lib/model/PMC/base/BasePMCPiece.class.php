<?php

abstract class BasePMCPiece extends Piece {

    public function configureTree() {
       $this->_root_class_name = 'PMC';
       $this->_tree_class_name = 'PMCPiece';
    }

}
