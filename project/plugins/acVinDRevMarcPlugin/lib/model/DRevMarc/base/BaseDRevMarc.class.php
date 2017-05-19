<?php
/**
 * BaseDRevMarc
 * 
 * Base model for DRevMarc
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $campagne
 * @property string $identifiant
 * @property string $etape
 * @property string $debut_distillation
 * @property string $fin_distillation
 * @property string $qte_marc
 * @property string $volume_obtenu
 * @property string $titre_alcool_vol
 * @property string $validation
 * @property string $validation_odg
 * @property integer $papier
 * @property acCouchdbJson $declarant
 * @property acCouchdbJson $mouvements
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
 * @method string getDebutDistillation()
 * @method string setDebutDistillation()
 * @method string getFinDistillation()
 * @method string setFinDistillation()
 * @method string getQteMarc()
 * @method string setQteMarc()
 * @method string getVolumeObtenu()
 * @method string setVolumeObtenu()
 * @method string getTitreAlcoolVol()
 * @method string setTitreAlcoolVol()
 * @method string getValidation()
 * @method string setValidation()
 * @method string getValidationOdg()
 * @method string setValidationOdg()
 * @method integer getPapier()
 * @method integer setPapier()
 * @method acCouchdbJson getDeclarant()
 * @method acCouchdbJson setDeclarant()
 * @method acCouchdbJson getMouvements()
 * @method acCouchdbJson setMouvements()
 * @method acCouchdbJson getPieces()
 * @method acCouchdbJson setPieces()
 
 */
 
abstract class BaseDRevMarc extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'DRevMarc';
    }
    
}