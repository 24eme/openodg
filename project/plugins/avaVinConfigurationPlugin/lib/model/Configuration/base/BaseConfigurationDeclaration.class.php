<?php
/**
 * BaseConfigurationDeclaration
 * 
 * Base model for ConfigurationDeclaration

 * @property string $no_usages_industriels
 * @property string $no_recapitulatif_couleur
 * @property ConfigurationDouane $douane
 * @property acCouchdbJson $relations

 * @method string getNoUsagesIndustriels()
 * @method string setNoUsagesIndustriels()
 * @method string getNoRecapitulatifCouleur()
 * @method string setNoRecapitulatifCouleur()
 * @method ConfigurationDouane getDouane()
 * @method ConfigurationDouane setDouane()
 * @method acCouchdbJson getRelations()
 * @method acCouchdbJson setRelations()
 
 */

abstract class BaseConfigurationDeclaration extends _ConfigurationDeclaration {
                
    public function configureTree() {
       $this->_root_class_name = 'Configuration';
       $this->_tree_class_name = 'ConfigurationDeclaration';
    }
                
}