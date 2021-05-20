<?php
/**
 * BaseTransactionMouvementFactures
 *
 * Base model for TransactionMouvementFactures

 * @property integer $facture
 * @property integer $facturable

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()

 */

abstract class BaseTransactionMouvementFactures extends MouvementFactures {

    public function configureTree() {
       $this->_root_class_name = 'Transaction';
       $this->_tree_class_name = 'TransactionMouvementFactures';
    }

}

