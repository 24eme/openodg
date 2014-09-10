<?php
/**
 * BaseDRev
 * 
 * Base model for DRev
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $campagne
 * @property string $identifiant
 * @property acCouchdbJson $declarant
 * @property DRevDeclaration $declaration
 * @property acCouchdbJson $prelevements

 * @method string get_id()
 * @method string set_id()
 * @method string get_rev()
 * @method string set_rev()
 * @method string getType()
 * @method string setType()
 * @method string getCampagne()
 * @method string setCampagne()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method acCouchdbJson getDeclarant()
 * @method acCouchdbJson setDeclarant()
 * @method DRevDeclaration getDeclaration()
 * @method DRevDeclaration setDeclaration()
 * @method acCouchdbJson getPrelevements()
 * @method acCouchdbJson setPrelevements()
 
 */
 
abstract class BaseDRev extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'DRev';
    }
    
}