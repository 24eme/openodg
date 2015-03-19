<?php
/**
 * BaseDegustation
 * 
 * Base model for Degustation
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $etape
 * @property string $identifiant
 * @property string $date
 * @property string $appellation
 * @property string $appellation_libelle
 * @property string $date_prelevement_debut
 * @property string $date_prelevement_fin
 * @property integer $nombre_commissions
 * @property string $heure
 * @property string $lieu
 * @property acCouchdbJson $operateurs
 * @property acCouchdbJson $degustateurs
 * @property acCouchdbJson $agents

 * @method string get_id()
 * @method string set_id()
 * @method string get_rev()
 * @method string set_rev()
 * @method string getType()
 * @method string setType()
 * @method string getEtape()
 * @method string setEtape()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getDate()
 * @method string setDate()
 * @method string getAppellation()
 * @method string setAppellation()
 * @method string getAppellationLibelle()
 * @method string setAppellationLibelle()
 * @method string getDatePrelevementDebut()
 * @method string setDatePrelevementDebut()
 * @method string getDatePrelevementFin()
 * @method string setDatePrelevementFin()
 * @method integer getNombreCommissions()
 * @method integer setNombreCommissions()
 * @method string getHeure()
 * @method string setHeure()
 * @method string getLieu()
 * @method string setLieu()
 * @method acCouchdbJson getOperateurs()
 * @method acCouchdbJson setOperateurs()
 * @method acCouchdbJson getDegustateurs()
 * @method acCouchdbJson setDegustateurs()
 * @method acCouchdbJson getAgents()
 * @method acCouchdbJson setAgents()
 
 */
 
abstract class BaseDegustation extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Degustation';
    }
    
}