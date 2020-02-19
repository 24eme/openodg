<?php
/**
 * BaseParcellaireIntentionAffectation
 * 
 * Base model for ParcellaireIntentionAffectation
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $identifiant
 * @property string $etape
 * @property string $validation
 * @property string $validation_odg
 * @property string $campagne
 * @property string $date
 * @property string $lecture_seule
 * @property string $signataire
 * @property integer $papier
 * @property ParcellaireIntentionAffectationDeclarant $declarant
 * @property ParcellaireIntentionAffectationDeclaration $declaration
 * @property string $observations

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getEtape()
 * @method string setEtape()
 * @method string getValidation()
 * @method string setValidation()
 * @method string getValidationOdg()
 * @method string setValidationOdg()
 * @method string getCampagne()
 * @method string setCampagne()
 * @method string getDate()
 * @method string setDate()
 * @method string getLectureSeule()
 * @method string setLectureSeule()
 * @method string getSignataire()
 * @method string setSignataire()
 * @method integer getPapier()
 * @method integer setPapier()
 * @method ParcellaireIntentionAffectationDeclarant getDeclarant()
 * @method ParcellaireIntentionAffectationDeclarant setDeclarant()
 * @method ParcellaireIntentionAffectationDeclaration getDeclaration()
 * @method ParcellaireIntentionAffectationDeclaration setDeclaration()
 * @method string getObservations()
 * @method string setObservations()
 
 */
 
abstract class BaseParcellaireIntentionAffectation extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'ParcellaireAffectation';
    }
    
}