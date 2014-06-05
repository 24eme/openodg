<?php
/**
 * BaseConfigurationCouleur
 * 
 * Base model for ConfigurationCouleur

 * @property float $rendement_couleur
 * @property float $rendement
 * @property string $libelle
 * @property string $libelle_long
 * @property ConfigurationDouane $douane

 * @method float getRendementCouleur()
 * @method float setRendementCouleur()
 * @method float getRendement()
 * @method float setRendement()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getLibelleLong()
 * @method string setLibelleLong()
 * @method ConfigurationDouane getDouane()
 * @method ConfigurationDouane setDouane()
 
 */

abstract class BaseConfigurationCouleur extends _ConfigurationDeclaration {
                
    public function configureTree() {
       $this->_root_class_name = 'Configuration';
       $this->_tree_class_name = 'ConfigurationCouleur';
    }
                
}