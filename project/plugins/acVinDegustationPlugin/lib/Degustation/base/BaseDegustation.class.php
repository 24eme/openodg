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
 * @property string $appellation_complement
 * @property string $organisme
 * @property string $millesime
 * @property string $libelle
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
 * @property string $reporte
 * @property string $motif_non_prelevement
 * @property string $signature_base64
 * @property acCouchdbJson $prelevements
 * @property acCouchdbJson $reports
 * @property DegustationLot $lots
 * @property acCouchdbJson $pieces

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method string getAppellation()
 * @method string setAppellation()
 * @method string getAppellationComplement()
 * @method string setAppellationComplement()
 * @method string getOrganisme()
 * @method string setOrganisme()
 * @method string getMillesime()
 * @method string setMillesime()
 * @method string getLibelle()
 * @method string setLibelle()
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
 * @method string getReporte()
 * @method string setReporte()
 * @method string getMotifNonPrelevement()
 * @method string setMotifNonPrelevement()
 * @method string getSignatureBase64()
 * @method string setSignatureBase64()
 * @method acCouchdbJson getPrelevements()
 * @method acCouchdbJson setPrelevements()
 * @method acCouchdbJson getReports()
 * @method acCouchdbJson setReports()
 * @method DegustationLot getLots()
 * @method DegustationLot setLots()
 * @method acCouchdbJson getPieces()
 * @method acCouchdbJson setPieces()
 
 */
 
abstract class BaseDegustation extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Degustation';
    }
    
}