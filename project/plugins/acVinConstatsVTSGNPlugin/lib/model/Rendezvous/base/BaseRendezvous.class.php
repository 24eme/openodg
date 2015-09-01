<?php
/**
 * BaseRendezvous
 * 
 * Base model for Rendezvous
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $type_rendezvous
 * @property string $libelle
 * @property string $identifiant
 * @property string $statut
 * @property string $date
 * @property string $heure
 * @property string $commentaire
 * @property string $raison_sociale
 * @property string $cvi
 * @property string $adresse
 * @property string $commune
 * @property string $code_postal
 * @property string $email
 * @property string $lat
 * @property string $lon

 * @method string get_id()
 * @method string set_id()
 * @method string get_rev()
 * @method string set_rev()
 * @method string getType()
 * @method string setType()
 * @method string getTypeRendezvous()
 * @method string setTypeRendezvous()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getStatut()
 * @method string setStatut()
 * @method string getDate()
 * @method string setDate()
 * @method string getHeure()
 * @method string setHeure()
 * @method string getCommentaire()
 * @method string setCommentaire()
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
 
 */
 
abstract class BaseRendezvous extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Rendezvous';
    }
    
}