<?php
/**
 * BaseDRaPProduit
 *
 * Base model DRaPProduit

 * @property string $libelle
 * @property acCouchdbJson $detail

 * @method string getLibelle()
 * @method string setLibelle()
 * @method acCouchdbJson getDetail()
 * @method acCouchdbJson setDetail()

 */

abstract class BaseDRaPProduit extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'DRaP';
       $this->_tree_class_name = 'DRaPProduit';
    }

}
