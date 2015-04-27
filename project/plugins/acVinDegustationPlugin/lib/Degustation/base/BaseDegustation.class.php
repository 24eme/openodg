<?php
/**
 * BaseDegustation
 * 
 * Base model for Degustation
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $appellation
 * @property string $appellation_libelle
 * @property string $date_degustation
 * @property string $identifiant
 * @property string $drev
 * @property string $raison_sociale
 * @property string $cvi
 * @property string $adresse
 * @property string $commune
 * @property string $code_postal
 * @property string $email
 * @property string $lat
 * @property string $lon
 * @property string $lng
 * @property string $date
 * @property string $date_prelevement
 * @property string $heure
 * @property string $position
 * @property string $agent
 * @property string $telephone_bureau
 * @property string $telephone_prive
 * @property string $telephone_mobile
 * @property string $date_demande
 * @property integer $reporte
 * @property string $motif_non_prelevement
 * @property acCouchdbJson $prelevements
 * @property DegustationLot $lots

 * @method string get_id()
 * @method string set_id()
 * @method string get_rev()
 * @method string set_rev()
 * @method string getType()
 * @method string setType()
 * @method string getAppellation()
 * @method string setAppellation()
 * @method string getAppellationLibelle()
 * @method string setAppellationLibelle()
 * @method string getDateDegustation()
 * @method string setDateDegustation()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getDrev()
 * @method string setDrev()
 * @method string getRaisonSociale()
 * @method string setRaisonSociale()
 * @method string getCvi()
 * @method string setCvi()
 * @method string getAdresse()
 * @method string setAdresse()
 * @method string getCommune()
 * @method string setCommune()
 * @method string getCodePostal()
 * @method string setCodePostal()
 * @method string getEmail()
 * @method string setEmail()
 * @method string getLat()
 * @method string setLat()
 * @method string getLon()
 * @method string setLon()
 * @method string getLng()
 * @method string setLng()
 * @method string getDate()
 * @method string setDate()
 * @method string getDatePrelevement()
 * @method string setDatePrelevement()
 * @method string getHeure()
 * @method string setHeure()
 * @method string getPosition()
 * @method string setPosition()
 * @method string getAgent()
 * @method string setAgent()
 * @method string getTelephoneBureau()
 * @method string setTelephoneBureau()
 * @method string getTelephonePrive()
 * @method string setTelephonePrive()
 * @method string getTelephoneMobile()
 * @method string setTelephoneMobile()
 * @method string getDateDemande()
 * @method string setDateDemande()
 * @method integer getReporte()
 * @method integer setReporte()
 * @method string getMotifNonPrelevement()
 * @method string setMotifNonPrelevement()
 * @method acCouchdbJson getPrelevements()
 * @method acCouchdbJson setPrelevements()
 * @method DegustationLot getLots()
 * @method DegustationLot setLots()
 
 */
 
abstract class BaseDegustation extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Degustation';
    }
    
}