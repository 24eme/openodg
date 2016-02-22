<?php
/**
 * BaseTirage
 * 
 * Base model for Tirage
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $campagne
 * @property string $identifiant
 * @property string $numero
 * @property string $etape
 * @property string $validation
 * @property string $validation_odg
 * @property integer $papier
 * @property acCouchdbJson $declarant
 * @property string $couleur
 * @property string $couleur_libelle
 * @property string $cepage
 * @property string $cepage_libelle
 * @property string $millesime
 * @property string $millesime_libelle
 * @property string $millesime_ventilation
 * @property string $fermentation_lactique
 * @property string $date_mise_en_bouteille_debut
 * @property string $date_mise_en_bouteille_fin
 * @property acCouchdbJson $composition

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
 * @method string getCouleur()
 * @method string setCouleur()
 * @method string getCouleurLibelle()
 * @method string setCouleurLibelle()
 * @method string getCepage()
 * @method string setCepage()
 * @method string getCepageLibelle()
 * @method string setCepageLibelle()
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
 
 */
 
abstract class BaseTirage extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Tirage';
    }
    
}