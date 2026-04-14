<?php
/**
 * BaseControle
 * 
 * Base model for Controle
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $date
 * @property string $date_tournee
 * @property string $type_tournee
 * @property string $agent_identifiant
 * @property string $identifiant
 * @property string $campagne
 * @property string $notification_date
 * @property ControleDeclarant $declarant
 * @property string $secteur
 * @property acCouchdbJson $liaisons_operateurs
 * @property string $liaisons
 * @property acCouchdbJson $audit
 * @property acCouchdbJson $parcelles
 * @property acCouchdbJson $manquements
 * @property string $superficie_totale
 * @property acCouchdbJson $surface_de_production
 * @property string $maturite
 * @property string $manquements_valides
 * @property acCouchdbJson $mouvements_statuts
 * @property acCouchdbJson $pieces

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method string getDate()
 * @method string setDate()
 * @method string getDateTournee()
 * @method string setDateTournee()
 * @method string getTypeTournee()
 * @method string setTypeTournee()
 * @method string getAgentIdentifiant()
 * @method string setAgentIdentifiant()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getCampagne()
 * @method string setCampagne()
 * @method string getNotificationDate()
 * @method string setNotificationDate()
 * @method ControleDeclarant getDeclarant()
 * @method ControleDeclarant setDeclarant()
 * @method string getSecteur()
 * @method string setSecteur()
 * @method acCouchdbJson getLiaisonsOperateurs()
 * @method acCouchdbJson setLiaisonsOperateurs()
 * @method string getLiaisons()
 * @method string setLiaisons()
 * @method acCouchdbJson getAudit()
 * @method acCouchdbJson setAudit()
 * @method acCouchdbJson getParcelles()
 * @method acCouchdbJson setParcelles()
 * @method acCouchdbJson getManquements()
 * @method acCouchdbJson setManquements()
 * @method string getSuperficieTotale()
 * @method string setSuperficieTotale()
 * @method acCouchdbJson getSurfaceDeProduction()
 * @method acCouchdbJson setSurfaceDeProduction()
 * @method string getMaturite()
 * @method string setMaturite()
 * @method string getManquementsValides()
 * @method string setManquementsValides()
 * @method acCouchdbJson getMouvementsStatuts()
 * @method acCouchdbJson setMouvementsStatuts()
 * @method acCouchdbJson getPieces()
 * @method acCouchdbJson setPieces()
 
 */
 
abstract class BaseControle extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Controle';
    }
    
}