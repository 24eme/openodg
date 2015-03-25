<?php
/**
 * BaseDegustationOperateur
 * 
 * Base model for DegustationOperateur

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
 * @property string $heure
 * @property string $position
 * @property string $agent
 * @property string $telephone_bureau
 * @property string $telephone_prive
 * @property string $telephone_mobile
 * @property string $date_demande
 * @property integer $reporte
 * @property acCouchdbJson $prelevements
 * @property acCouchdbJson $lots

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
 * @method acCouchdbJson getPrelevements()
 * @method acCouchdbJson setPrelevements()
 * @method acCouchdbJson getLots()
 * @method acCouchdbJson setLots()
 
 */

abstract class BaseDegustationOperateur extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Tournee';
       $this->_tree_class_name = 'DegustationOperateur';
    }
                
}