<?php
/**
 * BaseCompteChai
 * 
 * Base model for CompteChai

 * @property string $adresse
 * @property string $commune
 * @property string $code_postal
 * @property string $lat
 * @property string $lon
 * @property acCouchdbJson $attributs

 * @method string getAdresse()
 * @method string setAdresse()
 * @method string getCommune()
 * @method string setCommune()
 * @method string getCodePostal()
 * @method string setCodePostal()
 * @method string getLat()
 * @method string setLat()
 * @method string getLon()
 * @method string setLon()
 * @method acCouchdbJson getAttributs()
 * @method acCouchdbJson setAttributs()
 
 */

abstract class BaseCompteChai extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Compte';
       $this->_tree_class_name = 'CompteChai';
    }
                
}