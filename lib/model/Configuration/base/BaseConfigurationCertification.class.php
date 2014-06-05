<?php
/**
 * BaseConfigurationCertification
 * 
 * Base model for ConfigurationCertification

 * @property float $rendement
 * @property string $libelle
 * @property string $libelle_long
 * @property ConfigurationDouane $douane

 * @method float getRendement()
 * @method float setRendement()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getLibelleLong()
 * @method string setLibelleLong()
 * @method ConfigurationDouane getDouane()
 * @method ConfigurationDouane setDouane()
 
 */

abstract class BaseConfigurationCertification extends _ConfigurationDeclaration {
                
    public function configureTree() {
       $this->_root_class_name = 'Configuration';
       $this->_tree_class_name = 'ConfigurationCertification';
    }
                
}