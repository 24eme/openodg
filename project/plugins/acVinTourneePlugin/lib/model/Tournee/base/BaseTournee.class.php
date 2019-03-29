<?php
/**
 * BaseTournee
 * 
 * Base model for Tournee
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $etape
 * @property string $identifiant
 * @property string $statut
 * @property string $date
 * @property string $appellation
 * @property string $appellation_complement
 * @property string $millesime
 * @property string $appellation_libelle
 * @property string $libelle
 * @property string $produit
 * @property string $organisme
 * @property string $date_prelevement_debut
 * @property string $date_prelevement_fin
 * @property integer $nombre_commissions
 * @property integer $nombre_prelevements
 * @property string $heure
 * @property string $lieu
 * @property string $validation
 * @property string $agent_unique
 * @property string $type_tournee
 * @property acCouchdbJson $degustations
 * @property acCouchdbJson $degustateurs
 * @property acCouchdbJson $agents
 * @property acCouchdbJson $rendezvous

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method string getEtape()
 * @method string setEtape()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getStatut()
 * @method string setStatut()
 * @method string getDate()
 * @method string setDate()
 * @method string getAppellation()
 * @method string setAppellation()
 * @method string getAppellationComplement()
 * @method string setAppellationComplement()
 * @method string getMillesime()
 * @method string setMillesime()
 * @method string getAppellationLibelle()
 * @method string setAppellationLibelle()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getProduit()
 * @method string setProduit()
 * @method string getOrganisme()
 * @method string setOrganisme()
 * @method string getDatePrelevementDebut()
 * @method string setDatePrelevementDebut()
 * @method string getDatePrelevementFin()
 * @method string setDatePrelevementFin()
 * @method integer getNombreCommissions()
 * @method integer setNombreCommissions()
 * @method integer getNombrePrelevements()
 * @method integer setNombrePrelevements()
 * @method string getHeure()
 * @method string setHeure()
 * @method string getLieu()
 * @method string setLieu()
 * @method string getValidation()
 * @method string setValidation()
 * @method string getAgentUnique()
 * @method string setAgentUnique()
 * @method string getTypeTournee()
 * @method string setTypeTournee()
 * @method acCouchdbJson getDegustations()
 * @method acCouchdbJson setDegustations()
 * @method acCouchdbJson getDegustateurs()
 * @method acCouchdbJson setDegustateurs()
 * @method acCouchdbJson getAgents()
 * @method acCouchdbJson setAgents()
 * @method acCouchdbJson getRendezvous()
 * @method acCouchdbJson setRendezvous()
 
 */
 
abstract class BaseTournee extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Tournee';
    }
    
}