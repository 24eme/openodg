<?php
/**
 * BaseParcellaireAffectationProduit
 * 
 * Base model for ParcellaireAffectationProduit

 * @property string $libelle
 * @property acCouchdbJson $detail

 * @method string getLibelle()
 * @method string setLibelle()
 * @method acCouchdbJson getDetail()
 * @method acCouchdbJson setDetail()
 
 */

abstract class BaseParcellaireAffectationProduit extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'ParcellaireAffectation';
       $this->_tree_class_name = 'ParcellaireAffectationProduit';
    }
                
}