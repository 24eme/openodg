<?php

abstract class BaseCourrierPiece extends Piece {

    public function configureTree() {
       $this->_root_class_name = 'Courrier';
       $this->_tree_class_name = 'CourrierPiece';
    }

}
