<?php
/**
 * BaseParcellaire
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
 * @property integer $papier
 * @property acCouchdbJson $type_proprietaire
 * @property acCouchdbJson $acheteurs
 * @property acCouchdbJson $declarant
 * @property ParcellaireDeclaration $declaration

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
 * @method string getEtape()
 * @method string setEtape()
 * @method string getValidation()
 * @method string setValidation()
 * @method string getValidationOdg()
 * @method string setValidationOdg()
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
 
 */
 
abstract class BaseParcellaire extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Parcellaire';
    }
    
}