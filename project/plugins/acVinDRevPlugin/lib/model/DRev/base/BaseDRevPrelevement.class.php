<?php
/**
 * BaseDRevPrelevement
 * 
 * Base model for DRevPrelevement

 * @property string $total_lots
 * @property string $date
 * @property acCouchdbJson $lots

 * @method string getTotalLots()
 * @method string setTotalLots()
 * @method string getDate()
 * @method string setDate()
 * @method acCouchdbJson getLots()
 * @method acCouchdbJson setLots()
 
 */

abstract class BaseDRevPrelevement extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevPrelevement';
    }
                
}