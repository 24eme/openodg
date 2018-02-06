<?php
/**
 * BaseParcellaireIrrigableProduit
 * 
 * Base model for ParcellaireIrrigableProduit

 * @property string $libelle
 * @property acCouchdbJson $detail

 * @method string getLibelle()
 * @method string setLibelle()
 * @method acCouchdbJson getDetail()
 * @method acCouchdbJson setDetail()
 
 */

abstract class BaseParcellaireIrrigableProduit extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'ParcellaireIrrigable';
       $this->_tree_class_name = 'ParcellaireIrrigableProduit';
    }
                
}