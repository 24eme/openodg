<?php
/**
 * BaseRegistreVCI
 * 
 * Base model for RegistreVCI
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $campagne
 * @property string $identifiant
 * @property string $validation
 * @property string $validation_odg
 * @property integer $papier
 * @property integer $automatique
 * @property string $lecture_seule
 * @property string $version
 * @property RegistreVCIDeclaration $declaration
 * @property acCouchdbJson $mouvements

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
 * @method string getValidation()
 * @method string setValidation()
 * @method string getValidationOdg()
 * @method string setValidationOdg()
 * @method integer getPapier()
 * @method integer setPapier()
 * @method integer getAutomatique()
 * @method integer setAutomatique()
 * @method string getLectureSeule()
 * @method string setLectureSeule()
 * @method string getVersion()
 * @method string setVersion()
 * @method RegistreVCIDeclaration getDeclaration()
 * @method RegistreVCIDeclaration setDeclaration()
 * @method acCouchdbJson getMouvements()
 * @method acCouchdbJson setMouvements()
 
 */
 
abstract class BaseRegistreVCI extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'RegistreVCI';
    }
    
}