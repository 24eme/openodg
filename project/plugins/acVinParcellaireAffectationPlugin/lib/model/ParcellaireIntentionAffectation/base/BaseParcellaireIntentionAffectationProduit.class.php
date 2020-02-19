<?php
/**
 * BaseParcellaireIntentionAffectationProduit
 * 
 * Base model for ParcellaireIntentionAffectationProduit

 * @property string $libelle
 * @property acCouchdbJson $detail

 * @method string getLibelle()
 * @method string setLibelle()
 * @method acCouchdbJson getDetail()
 * @method acCouchdbJson setDetail()
 
 */

abstract class BaseParcellaireIntentionAffectationProduit extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'ParcellaireIntentionAffectation';
       $this->_tree_class_name = 'ParcellaireIntentionAffectationProduit';
    }
                
}