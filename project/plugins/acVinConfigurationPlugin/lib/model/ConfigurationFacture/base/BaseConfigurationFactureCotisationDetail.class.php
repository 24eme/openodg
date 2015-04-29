<?php
/**
 * BaseConfigurationFactureCotisationDetail
 * 
 * Base model for ConfigurationFactureCotisationDetail

 * @property string $modele
 * @property string $callback
 * @property string $prix
 * @property string $variable
 * @property string $tranche
 * @property string $reference
 * @property string $libelle
 * @property string $complement_libelle
 * @property acCouchdbJson $docs

 * @method string getModele()
 * @method string setModele()
 * @method string getCallback()
 * @method string setCallback()
 * @method string getPrix()
 * @method string setPrix()
 * @method string getVariable()
 * @method string setVariable()
 * @method string getTranche()
 * @method string setTranche()
 * @method string getReference()
 * @method string setReference()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getComplementLibelle()
 * @method string setComplementLibelle()
 * @method acCouchdbJson getDocs()
 * @method acCouchdbJson setDocs()
 
 */

abstract class BaseConfigurationFactureCotisationDetail extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'ConfigurationFacture';
       $this->_tree_class_name = 'ConfigurationFactureCotisationDetail';
    }
                
}