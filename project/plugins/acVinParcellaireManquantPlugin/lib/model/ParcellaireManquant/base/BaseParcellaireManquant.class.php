<?php
/**
 * BaseParcellaireManquant
 *
 * Base model for ParcellaireManquant
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $campagne
 * @property string $identifiant
 * @property string $etape
 * @property string $validation
 * @property string $validation_odg
 * @property string $signataire
 * @property integer $papier
 * @property ParcellaireManquantDeclarant $declarant
 * @property ParcellaireManquantDeclaration $declaration
 * @property string $observations
 * @property acCouchdbJson $pieces

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
 * @method string getSignataire()
 * @method string setSignataire()
 * @method integer getPapier()
 * @method integer setPapier()
 * @method ParcellaireManquantDeclarant getDeclarant()
 * @method ParcellaireManquantDeclarant setDeclarant()
 * @method ParcellaireManquantDeclaration getDeclaration()
 * @method ParcellaireManquantDeclaration setDeclaration()
 * @method string getObservations()
 * @method string setObservations()
 * @method acCouchdbJson getPieces()
 * @method acCouchdbJson setPieces()

 */

abstract class BaseParcellaireManquant extends DeclarationParcellaire {

    public function getDocumentDefinitionModel() {
        return 'ParcellaireManquant';
    }

}
