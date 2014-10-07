<?php
/**
 * BaseDRev
 * 
 * Base model for DRev
 *
 * @property string $_id
 * @property string $_rev
 * @property acCouchdbJson $_attachments
 * @property string $type
 * @property string $campagne
 * @property string $identifiant
 * @property string $validation
 * @property acCouchdbJson $declarant
 * @property DRevDeclaration $declaration
 * @property acCouchdbJson $prelevements
 * @property acCouchdbJson $chais

 * @method string get_id()
 * @method string set_id()
 * @method string get_rev()
 * @method string set_rev()
 * @method acCouchdbJson get_attachments()
 * @method acCouchdbJson set_attachments()
 * @method string getType()
 * @method string setType()
 * @method string getCampagne()
 * @method string setCampagne()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getValidation()
 * @method string setValidation()
 * @method acCouchdbJson getDeclarant()
 * @method acCouchdbJson setDeclarant()
 * @method DRevDeclaration getDeclaration()
 * @method DRevDeclaration setDeclaration()
 * @method acCouchdbJson getPrelevements()
 * @method acCouchdbJson setPrelevements()
 * @method acCouchdbJson getChais()
 * @method acCouchdbJson setChais()
 
 */
 
abstract class BaseDRev extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'DRev';
    }
    
}