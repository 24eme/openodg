<?php
/**
 * BaseParcellaireProduit
 * 
 * Base model for ParcellaireProduit

 * @property string $libelle
 * @property acCouchdbJson $detail

 * @method string getLibelle()
 * @method string setLibelle()
 * @method acCouchdbJson getDetail()
 * @method acCouchdbJson setDetail()
 
 */

abstract class BaseParcellaireProduit extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Parcellaire';
       $this->_tree_class_name = 'ParcellaireProduit';
    }
                
}