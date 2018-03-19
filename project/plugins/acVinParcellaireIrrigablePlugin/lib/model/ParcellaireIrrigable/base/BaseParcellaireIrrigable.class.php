<?php
/**
 * BaseParcellaireIrrigable
 * 
 * Base model for ParcellaireIrrigable
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $campagne
 * @property string $identifiant
 * @property string $etape
 * @property string $validation
 * @property string $validation_odg
 * @property acCouchdbJson $declarant
 * @property ParcellaireIrrigableDeclaration $declaration

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method string getCampagne()
 * @method string setCampagne()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getEtape()
 * @method string setEtape()
 * @method string getValidation()
 * @method string setValidation()
 * @method string getValidationOdg()
 * @method string setValidationOdg()
 * @method acCouchdbJson getDeclarant()
 * @method acCouchdbJson setDeclarant()
 * @method ParcellaireIrrigableDeclaration getDeclaration()
 * @method ParcellaireIrrigableDeclaration setDeclaration()
 
 */
 
abstract class BaseParcellaireIrrigable extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'ParcellaireIrrigable';
    }
    
}