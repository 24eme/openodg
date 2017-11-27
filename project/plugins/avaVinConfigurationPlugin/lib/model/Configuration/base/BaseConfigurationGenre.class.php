<?php
/**
 * BaseConfigurationGenre
 * 
 * Base model for ConfigurationGenre

 * @property float $rendement
 * @property string $libelle
 * @property string $libelle_long
 * @property acCouchdbJson $relations
 * @property ConfigurationDouane $douane

 * @method float getRendement()
 * @method float setRendement()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getLibelleLong()
 * @method string setLibelleLong()
 * @method acCouchdbJson getRelations()
 * @method acCouchdbJson setRelations()
 * @method ConfigurationDouane getDouane()
 * @method ConfigurationDouane setDouane()
 
 */

abstract class BaseConfigurationGenre extends _ConfigurationDeclaration {
                
    public function configureTree() {
       $this->_root_class_name = 'Configuration';
       $this->_tree_class_name = 'ConfigurationGenre';
    }
                
}