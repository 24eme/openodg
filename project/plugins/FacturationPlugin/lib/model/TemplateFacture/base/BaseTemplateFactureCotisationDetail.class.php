<?php
/**
 * BaseTemplateFactureCotisationDetail
 * 
 * Base model for TemplateFactureCotisationDetail

 * @property string $modele
 * @property string $prix
 * @property string $tva
 * @property string $libelle
 * @property string $variable
 * @property string $tranche
 * @property string $reference
 * @property string $callback
 * @property string $complement_libelle
 * @property acCouchdbJson $docs

 * @method string getModele()
 * @method string setModele()
 * @method string getPrix()
 * @method string setPrix()
 * @method string getTva()
 * @method string setTva()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getVariable()
 * @method string setVariable()
 * @method string getTranche()
 * @method string setTranche()
 * @method string getReference()
 * @method string setReference()
 * @method string getCallback()
 * @method string setCallback()
 * @method string getComplementLibelle()
 * @method string setComplementLibelle()
 * @method acCouchdbJson getDocs()
 * @method acCouchdbJson setDocs()
 
 */

abstract class BaseTemplateFactureCotisationDetail extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'TemplateFacture';
       $this->_tree_class_name = 'TemplateFactureCotisationDetail';
    }
                
}