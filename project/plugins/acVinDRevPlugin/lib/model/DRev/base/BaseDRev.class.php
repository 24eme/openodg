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
 * @property DRevPrelevements $prelevements
 * @property DRevDeclaration $declaration
 * @property acCouchdbJson $lots

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
 * @method DRevPrelevements getPrelevements()
 * @method DRevPrelevements setPrelevements()
 * @method DRevDeclaration getDeclaration()
 * @method DRevDeclaration setDeclaration()
 * @method acCouchdbJson getLots()
 * @method acCouchdbJson setLots()
 
 */
 
abstract class BaseDRev extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'DRev';
    }
    
}