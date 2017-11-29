<?php
/**
 * BaseEtablissement
 * 
 * Base model for Etablissement
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $identifiant
 * @property string $raison_sociale
 * @property string $nom
 * @property string $cvi
 * @property string $siren
 * @property string $siret
 * @property string $email
 * @property string $telephone_bureau
 * @property string $telephone_mobile
 * @property string $telephone_prive
 * @property string $fax
 * @property string $adresse
 * @property string $code_postal
 * @property string $code_insee
 * @property string $commune
 * @property string $date_premiere_connexion
 * @property string $compte_id
 * @property acCouchdbJson $familles
 * @property acCouchdbJson $chais
 * @property acCouchdbJson $droits

 * @method string get_id()
 * @method string set_id()
 * @method string get_rev()
 * @method string set_rev()
 * @method string getType()
 * @method string setType()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getRaisonSociale()
 * @method string setRaisonSociale()
 * @method string getNom()
 * @method string setNom()
 * @method string getCvi()
 * @method string setCvi()
 * @method string getSiren()
 * @method string setSiren()
 * @method string getSiret()
 * @method string setSiret()
 * @method string getEmail()
 * @method string setEmail()
 * @method string getTelephoneBureau()
 * @method string setTelephoneBureau()
 * @method string getTelephoneMobile()
 * @method string setTelephoneMobile()
 * @method string getTelephonePrive()
 * @method string setTelephonePrive()
 * @method string getFax()
 * @method string setFax()
 * @method string getAdresse()
 * @method string setAdresse()
 * @method string getCodePostal()
 * @method string setCodePostal()
 * @method string getCodeInsee()
 * @method string setCodeInsee()
 * @method string getCommune()
 * @method string setCommune()
 * @method string getDatePremiereConnexion()
 * @method string setDatePremiereConnexion()
 * @method string getCompteId()
 * @method string setCompteId()
 * @method acCouchdbJson getFamilles()
 * @method acCouchdbJson setFamilles()
 * @method acCouchdbJson getChais()
 * @method acCouchdbJson setChais()
 * @method acCouchdbJson getDroits()
 * @method acCouchdbJson setDroits()
 
 */
 
abstract class BaseEtablissement extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Etablissement';
    }
    
}