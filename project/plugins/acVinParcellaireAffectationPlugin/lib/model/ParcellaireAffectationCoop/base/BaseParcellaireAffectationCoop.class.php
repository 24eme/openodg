<?php
/**
 * BaseParcellaireAffectationCoop
 * 
 * Base model for ParcellaireAffectationCoop
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $campagne
 * @property string $identifiant
 * @property string $etape
 * @property ParcellaireAffectationCoopDeclarant $declarant
 * @property acCouchdbJson $apporteurs

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
 * @method ParcellaireAffectationCoopDeclarant getDeclarant()
 * @method ParcellaireAffectationCoopDeclarant setDeclarant()
 * @method acCouchdbJson getApporteurs()
 * @method acCouchdbJson setApporteurs()
 
 */
 
abstract class BaseParcellaireAffectationCoop extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'ParcellaireAffectationCoop';
    }
    
}