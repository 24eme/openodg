<?php

abstract class BaseTransactionPiece extends Piece {

    public function configureTree() {
       $this->_root_class_name = 'Transaction';
       $this->_tree_class_name = 'TransactionPiece';
    }

}
