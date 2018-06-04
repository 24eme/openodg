<?php
/**
 * BaseParcellaireIrrigueProduit
 * 
 * Base model for ParcellaireIrrigueProduit

 * @property string $libelle
 * @property acCouchdbJson $detail

 * @method string getLibelle()
 * @method string setLibelle()
 * @method acCouchdbJson getDetail()
 * @method acCouchdbJson setDetail()
 
 */

abstract class BaseParcellaireIrrigueProduit extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'ParcellaireIrrigue';
       $this->_tree_class_name = 'ParcellaireIrrigueProduit';
    }
                
}