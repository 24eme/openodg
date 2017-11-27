<?php
/**
 * BaseConfigurationDouane
 * 
 * Base model for ConfigurationDouane

 * @property string $type_aoc
 * @property string $couleur
 * @property string $appellation_lieu
 * @property string $qualite
 * @property string $code_cepage

 * @method string getTypeAoc()
 * @method string setTypeAoc()
 * @method string getCouleur()
 * @method string setCouleur()
 * @method string getAppellationLieu()
 * @method string setAppellationLieu()
 * @method string getQualite()
 * @method string setQualite()
 * @method string getCodeCepage()
 * @method string setCodeCepage()
 
 */

abstract class BaseConfigurationDouane extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Configuration';
       $this->_tree_class_name = 'ConfigurationDouane';
    }
                
}