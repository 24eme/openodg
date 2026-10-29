<?php

abstract class BaseConditionnementPiece extends Piece {

    public function configureTree() {
       $this->_root_class_name = 'Conditionnement';
       $this->_tree_class_name = 'ConditionnementPiece';
    }

}
