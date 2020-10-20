<?php


abstract class BaseChgtDenomPiece extends Piece {

    public function configureTree() {
       $this->_root_class_name = 'ChgtDenom';
       $this->_tree_class_name = 'ChgtDenomPiece';
    }

}
