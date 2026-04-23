<?php
/**
 * BaseDRapProduit
 *
 * Base model DRapProduit

 * @property string $libelle
 * @property acCouchdbJson $detail

 * @method string getLibelle()
 * @method string setLibelle()
 * @method acCouchdbJson getDetail()
 * @method acCouchdbJson setDetail()

 */

abstract class BaseDRapProduit extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'DRap';
       $this->_tree_class_name = 'DRapProduit';
    }

}
