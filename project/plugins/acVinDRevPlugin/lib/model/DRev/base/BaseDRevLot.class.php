<?php
/**
 * BaseDRevLot
 * 
 * Base model for DRevLot

 * @property integer $total
 * @property acCouchdbJson $produits

 * @method integer getTotal()
 * @method integer setTotal()
 * @method acCouchdbJson getProduits()
 * @method acCouchdbJson setProduits()
 
 */

abstract class BaseDRevLot extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevLot';
    }
                
}