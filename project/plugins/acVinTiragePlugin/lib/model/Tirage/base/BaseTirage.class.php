<?php
/**
 * BaseTirage
 * 
 * Base model for Tirage
 *
 * @property string $_id
 * @property string $_rev
 * @property acCouchdbJson $_attachments
 * @property string $type
 * @property string $campagne
 * @property string $identifiant
 * @property string $numero
 * @property string $etape
 * @property string $validation
 * @property string $validation_odg
 * @property integer $papier
 * @property acCouchdbJson $declarant
 * @property string $qualite
 * @property string $lieu_stockage
 * @property string $couleur
 * @property string $couleur_libelle
 * @property acCouchdbJson $cepages
 * @property string $millesime
 * @property string $millesime_libelle
 * @property string $millesime_ventilation
 * @property string $fermentation_lactique
 * @property string $date_mise_en_bouteille_debut
 * @property string $date_mise_en_bouteille_fin
 * @property acCouchdbJson $composition
 * @property TirageDocuments $documents
 * @property acCouchdbJson $pieces

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method acCouchdbJson getAttachments()
 * @method acCouchdbJson setAttachments()
 * @method string getType()
 * @method string setType()
 * @method string getCampagne()
 * @method string setCampagne()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getNumero()
 * @method string setNumero()
 * @method string getEtape()
 * @method string setEtape()
 * @method string getValidation()
 * @method string setValidation()
 * @method string getValidationOdg()
 * @method string setValidationOdg()
 * @method integer getPapier()
 * @method integer setPapier()
 * @method acCouchdbJson getDeclarant()
 * @method acCouchdbJson setDeclarant()
 * @method string getQualite()
 * @method string setQualite()
 * @method string getLieuStockage()
 * @method string setLieuStockage()
 * @method string getCouleur()
 * @method string setCouleur()
 * @method string getCouleurLibelle()
 * @method string setCouleurLibelle()
 * @method acCouchdbJson getCepages()
 * @method acCouchdbJson setCepages()
 * @method string getMillesime()
 * @method string setMillesime()
 * @method string getMillesimeLibelle()
 * @method string setMillesimeLibelle()
 * @method string getMillesimeVentilation()
 * @method string setMillesimeVentilation()
 * @method string getFermentationLactique()
 * @method string setFermentationLactique()
 * @method string getDateMiseEnBouteilleDebut()
 * @method string setDateMiseEnBouteilleDebut()
 * @method string getDateMiseEnBouteilleFin()
 * @method string setDateMiseEnBouteilleFin()
 * @method acCouchdbJson getComposition()
 * @method acCouchdbJson setComposition()
 * @method TirageDocuments getDocuments()
 * @method TirageDocuments setDocuments()
 * @method acCouchdbJson getPieces()
 * @method acCouchdbJson setPieces()
 
 */
 
abstract class BaseTirage extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Tirage';
    }
    
}