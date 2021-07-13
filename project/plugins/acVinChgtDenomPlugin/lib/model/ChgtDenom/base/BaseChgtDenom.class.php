<?php
/**
 * BaseChgtDenom
 * 
 * Base model for ChgtDenom
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $date
 * @property string $campagne
 * @property string $etape
 * @property string $validation
 * @property string $validation_odg
 * @property string $identifiant
 * @property integer $papier
 * @property string $changement_origine_mvtkey
 * @property string $changement_produit_hash
 * @property string $changement_produit_libelle
 * @property acCouchdbJson $changement_cepages
 * @property float $changement_volume
 * @property ChgtDenomDeclarant $declarant
 * @property acCouchdbJson $lots
 * @property acCouchdbJson $mouvements
 * @property acCouchdbJson $mouvements_lots
 * @property acCouchdbJson $pieces

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method string getDate()
 * @method string setDate()
 * @method string getCampagne()
 * @method string setCampagne()
 * @method string getEtape()
 * @method string setEtape()
 * @method string getValidation()
 * @method string setValidation()
 * @method string getValidationOdg()
 * @method string setValidationOdg()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method integer getPapier()
 * @method integer setPapier()
 * @method string getChangementOrigineMvtkey()
 * @method string setChangementOrigineMvtkey()
 * @method string getChangementProduit()
 * @method string setChangementProduitHash()
 * @method string getChangementProduitLibelle()
 * @method string setChangementProduitLibelle()
 * @method acCouchdbJson getChangementCepages()
 * @method acCouchdbJson setChangementCepages()
 * @method float getChangementVolume()
 * @method float setChangementVolume()
 * @method ChgtDenomDeclarant getDeclarant()
 * @method ChgtDenomDeclarant setDeclarant()
 * @method acCouchdbJson getLots()
 * @method acCouchdbJson setLots()
 * @method acCouchdbJson getMouvements()
 * @method acCouchdbJson setMouvements()
 * @method acCouchdbJson getMouvementsLots()
 * @method acCouchdbJson setMouvementsLots()
 * @method acCouchdbJson getPieces()
 * @method acCouchdbJson setPieces()
 
 */

abstract class BaseChgtDenom extends  DeclarationLots {

    public function getDocumentDefinitionModel() {
        return 'ChgtDenom';
    }
    
}