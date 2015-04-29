<?php
/**
 * BaseConfigurationFacture
 * 
 * Base model for ConfigurationFacture
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $campagne
 * @property acCouchdbJson $types

 * @method string get_id()
 * @method string set_id()
 * @method string get_rev()
 * @method string set_rev()
 * @method string getType()
 * @method string setType()
 * @method string getCampagne()
 * @method string setCampagne()
 * @method acCouchdbJson getTypes()
 * @method acCouchdbJson setTypes()
 
 */
 
abstract class BaseConfigurationFacture extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'ConfigurationFacture';
    }
    
}