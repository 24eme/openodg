<?php
/**
 * BaseConfigurationLieu
 * 
 * Base model for ConfigurationLieu

 * @property float $rendement
 * @property float $rendement_mention
 * @property float $rendement_appellation
 * @property string $libelle
 * @property string $libelle_long
 * @property ConfigurationDouane $douane

 * @method float getRendement()
 * @method float setRendement()
 * @method float getRendementMention()
 * @method float setRendementMention()
 * @method float getRendementAppellation()
 * @method float setRendementAppellation()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getLibelleLong()
 * @method string setLibelleLong()
 * @method ConfigurationDouane getDouane()
 * @method ConfigurationDouane setDouane()
 
 */

abstract class BaseConfigurationLieu extends _ConfigurationDeclaration {
                
    public function configureTree() {
       $this->_root_class_name = 'Configuration';
       $this->_tree_class_name = 'ConfigurationLieu';
    }
                
}