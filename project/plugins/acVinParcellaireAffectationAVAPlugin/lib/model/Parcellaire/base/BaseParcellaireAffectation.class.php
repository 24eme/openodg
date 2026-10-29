<?php
/**
 * BaseParcellaireAffectation
 *
 * Base model for Parcellaire
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $campagne
 * @property string $identifiant
 * @property string $etape
 * @property string $validation
 * @property string $validation_odg
 * @property string $autorisation_acheteur
 * @property integer $papier
 * @property acCouchdbJson $type_proprietaire
 * @property acCouchdbJson $acheteurs
 * @property acCouchdbJson $declarant
 * @property ParcellaireDeclaration $declaration
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
 * @method string getAutorisationAcheteur()
 * @method string setAutorisationAcheteur()
 * @method integer getPapier()
 * @method integer setPapier()
 * @method acCouchdbJson getTypeProprietaire()
 * @method acCouchdbJson setTypeProprietaire()
 * @method acCouchdbJson getAcheteurs()
 * @method acCouchdbJson setAcheteurs()
 * @method acCouchdbJson getDeclarant()
 * @method acCouchdbJson setDeclarant()
 * @method ParcellaireDeclaration getDeclaration()
 * @method ParcellaireDeclaration setDeclaration()
 * @method acCouchdbJson getPieces()
 * @method acCouchdbJson setPieces()

 */

abstract class BaseParcellaireAffectation extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'ParcellaireAffectation';
    }

}