<?php
/**
 * BaseParcellaireManquantProduit
 *
 * Base model for ParcellaireManquantProduit

 * @property string $libelle
 * @property acCouchdbJson $detail

 * @method string getLibelle()
 * @method string setLibelle()
 * @method acCouchdbJson getDetail()
 * @method acCouchdbJson setDetail()

 */

abstract class BaseParcellaireManquantProduit extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireManquant';
       $this->_tree_class_name = 'ParcellaireManquantProduit';
    }

}
