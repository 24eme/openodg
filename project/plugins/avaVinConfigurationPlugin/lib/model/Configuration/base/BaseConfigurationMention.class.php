<?php
/**
 * BaseConfigurationMention
 * 
 * Base model for ConfigurationMention

 * @property string $libelle
 * @property string $libelle_long
 * @property float $rendement
 * @property float $rendement_mention
 * @property ConfigurationDouane $douane
 * @property acCouchdbJson $relations

 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getLibelleLong()
 * @method string setLibelleLong()
 * @method float getRendement()
 * @method float setRendement()
 * @method float getRendementMention()
 * @method float setRendementMention()
 * @method ConfigurationDouane getDouane()
 * @method ConfigurationDouane setDouane()
 * @method acCouchdbJson getRelations()
 * @method acCouchdbJson setRelations()
 
 */

abstract class BaseConfigurationMention extends _ConfigurationDeclaration {
                
    public function configureTree() {
       $this->_root_class_name = 'Configuration';
       $this->_tree_class_name = 'ConfigurationMention';
    }
                
}