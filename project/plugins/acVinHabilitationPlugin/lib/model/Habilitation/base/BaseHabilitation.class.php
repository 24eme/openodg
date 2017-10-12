<?php
/**
 * BaseHabilitation
 * 
 * Base model for Habilitation
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $identifiant
 * @property string $validation
 * @property string $validation_odg
 * @property string $etape
 * @property string $date
 * @property integer $non_recoltant
 * @property integer $non_conditionneur
 * @property integer $non_vinificateur
 * @property integer $papier
 * @property integer $automatique
 * @property string $lecture_seule
 * @property string $version
 * @property acCouchdbJson $declarant
 * @property HabilitationDeclaration $declaration
 * @property acCouchdbJson $historique

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getValidation()
 * @method string setValidation()
 * @method string getValidationOdg()
 * @method string setValidationOdg()
 * @method string getEtape()
 * @method string setEtape()
 * @method string getDate()
 * @method string setDate()
 * @method integer getNonRecoltant()
 * @method integer setNonRecoltant()
 * @method integer getNonConditionneur()
 * @method integer setNonConditionneur()
 * @method integer getNonVinificateur()
 * @method integer setNonVinificateur()
 * @method integer getPapier()
 * @method integer setPapier()
 * @method integer getAutomatique()
 * @method integer setAutomatique()
 * @method string getLectureSeule()
 * @method string setLectureSeule()
 * @method string getVersion()
 * @method string setVersion()
 * @method acCouchdbJson getDeclarant()
 * @method acCouchdbJson setDeclarant()
 * @method HabilitationDeclaration getDeclaration()
 * @method HabilitationDeclaration setDeclaration()
 * @method acCouchdbJson getHistorique()
 * @method acCouchdbJson setHistorique()
 
 */
 
abstract class BaseHabilitation extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Habilitation';
    }
    
}