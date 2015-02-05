<?php
/**
 * BaseParcellaireCepageDetail
 * 
 * Base model for ParcellaireCepageDetail

 * @property float $superficie
 * @property string $commune
 * @property string $identifiant_parcelle
 * @property string $numero_parcelle

 * @method float getSuperficie()
 * @method float setSuperficie()
 * @method string getCommune()
 * @method string setCommune()
 * @method string getIdentifiantParcelle()
 * @method string setIdentifiantParcelle()
 * @method string getNumeroParcelle()
 * @method string setNumeroParcelle()
 
 */

abstract class BaseParcellaireCepageDetail extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Parcellaire';
       $this->_tree_class_name = 'ParcellaireCepageDetail';
    }
                
}