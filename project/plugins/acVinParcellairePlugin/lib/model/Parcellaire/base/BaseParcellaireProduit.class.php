<?php
/**
 * BaseParcellaieProduit
 *
 * Base model for ParcellaieProduit

 * @property string $libelle
 * @property acCouchdbJson $acheteurs
 * @property acCouchdbJson $detail

 * @method string getLibelle()
 * @method string setLibelle()
 * @method acCouchdbJson getAcheteurs()
 * @method acCouchdbJson setAcheteurs()
 * @method acCouchdbJson getDetail()
 * @method acCouchdbJson setDetail()

 */

abstract class BaseParcellaireProduit extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'Parcellaire';
       $this->_tree_class_name = 'ParcellaireProduit';
    }

}
