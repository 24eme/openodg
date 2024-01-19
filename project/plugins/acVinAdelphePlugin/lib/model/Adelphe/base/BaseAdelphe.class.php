<?php
/**
 * BaseAdelphe
 * 
 * Base model for Adelphe
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $campagne
 * @property string $identifiant
 * @property string $volume_conditionne_total
 * @property string $volume_conditionne_bib
 * @property string $volume_conditionne_bouteille
 * @property string $contribution_regime_bib
 * @property string $contribution_taux
 * @property string $contribution_valeur
 * @property string $validation
 * @property string $validation_odg
 * @property AdelpheDeclarant $declarant

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
 * @method string getVolumeConditionneTotal()
 * @method string setVolumeConditionneTotal()
 * @method string getVolumeConditionneBib()
 * @method string setVolumeConditionneBib()
 * @method string getVolumeConditionneBouteille()
 * @method string setVolumeConditionneBouteille()
 * @method string getContributionRegimeBib()
 * @method string setContributionRegimeBib()
 * @method string getContributionTaux()
 * @method string setContributionTaux()
 * @method string getContributionValeur()
 * @method string setContributionValeur()
 * @method string getValidation()
 * @method string setValidation()
 * @method string getValidationOdg()
 * @method string setValidationOdg()
 * @method AdelpheDeclarant getDeclarant()
 * @method AdelpheDeclarant setDeclarant()
 
 */
 
abstract class BaseAdelphe extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Adelphe';
    }
    
}