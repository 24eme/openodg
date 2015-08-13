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
 * @property string $etape
 * @property string $identifiant
 * @property string $validation
 * @property string $validation_odg
 * @property integer $non_recoltant
 * @property integer $non_conditionneur
 * @property integer $non_vinificateur
 * @property integer $papier
 * @property integer $automatique
 * @property acCouchdbJson $declarant
 * @property DRevDeclaration $declaration
 * @property acCouchdbJson $prelevements
 * @property DRevDocuments $documents
 * @property string $documents_rappel
 * @property acCouchdbJson $documents_rappels
 * @property acCouchdbJson $facturable
 * @property acCouchdbJson $chais
 * @property acCouchdbJson $mouvements

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
 * @method string getEtape()
 * @method string setEtape()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getValidation()
 * @method string setValidation()
 * @method string getValidationOdg()
 * @method string setValidationOdg()
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
 * @method acCouchdbJson getDeclarant()
 * @method acCouchdbJson setDeclarant()
 * @method DRevDeclaration getDeclaration()
 * @method DRevDeclaration setDeclaration()
 * @method acCouchdbJson getPrelevements()
 * @method acCouchdbJson setPrelevements()
 * @method DRevDocuments getDocuments()
 * @method DRevDocuments setDocuments()
 * @method string getDocumentsRappel()
 * @method string setDocumentsRappel()
 * @method acCouchdbJson getDocumentsRappels()
 * @method acCouchdbJson setDocumentsRappels()
 * @method acCouchdbJson getFacturable()
 * @method acCouchdbJson setFacturable()
 * @method acCouchdbJson getChais()
 * @method acCouchdbJson setChais()
 * @method acCouchdbJson getMouvements()
 * @method acCouchdbJson setMouvements()
 
 */
 
abstract class BaseDRev extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'DRev';
    }
    
}