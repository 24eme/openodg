<?php
/**
 * BaseConfigurationFactureCotisation
 * 
 * Base model for ConfigurationFactureCotisation

 * @property string $modele
 * @property string $callback
 * @property string $libelle
 * @property acCouchdbJson $details

 * @method string getModele()
 * @method string setModele()
 * @method string getCallback()
 * @method string setCallback()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method acCouchdbJson getDetails()
 * @method acCouchdbJson setDetails()
 
 */

abstract class BaseConfigurationFactureCotisation extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'ConfigurationFacture';
       $this->_tree_class_name = 'ConfigurationFactureCotisation';
    }
                
}