<?php
/**
 * BaseTravauxMarc
 * 
 * Base model for TravauxMarc
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $campagne
 * @property string $identifiant
 * @property string $etape
 * @property acCouchdbJson $fournisseurs
 * @property string $date_distillation
 * @property string $distillation_prestataire
 * @property string $alambic_connu
 * @property acCouchdbJson $adresse_distillation
 * @property string $validation
 * @property string $validation_odg
 * @property integer $papier
 * @property acCouchdbJson $declarant
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
 * @method acCouchdbJson getFournisseurs()
 * @method acCouchdbJson setFournisseurs()
 * @method string getDateDistillation()
 * @method string setDateDistillation()
 * @method string getDistillationPrestataire()
 * @method string setDistillationPrestataire()
 * @method string getAlambicConnu()
 * @method string setAlambicConnu()
 * @method acCouchdbJson getAdresseDistillation()
 * @method acCouchdbJson setAdresseDistillation()
 * @method string getValidation()
 * @method string setValidation()
 * @method string getValidationOdg()
 * @method string setValidationOdg()
 * @method integer getPapier()
 * @method integer setPapier()
 * @method acCouchdbJson getDeclarant()
 * @method acCouchdbJson setDeclarant()
 * @method acCouchdbJson getPieces()
 * @method acCouchdbJson setPieces()
 
 */
 
abstract class BaseTravauxMarc extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'TravauxMarc';
    }
    
}