<?php
/**
 * BaseTourneeRendezVous
 * 
 * Base model for TourneeRendezVous

 * @property string $heure
 * @property string $compte_identifiant
 * @property string $compte_cvi
 * @property string $compte_raison_sociale
 * @property string $compte_commune
 * @property string $compte_adresse
 * @property string $compte_code_postal
 * @property string $compte_lon
 * @property string $compte_lat
 * @property string $compte_telephone_bureau
 * @property string $compte_telephone_prive
 * @property string $compte_telephone_mobile
 * @property string $compte_email
 * @property string $type_rendezvous
 * @property string $rendezvous_commentaire
 * @property string $nom_agent_origine
 * @property string $constat
 * @property string $position

 * @method string getHeure()
 * @method string setHeure()
 * @method string getCompteIdentifiant()
 * @method string setCompteIdentifiant()
 * @method string getCompteCvi()
 * @method string setCompteCvi()
 * @method string getCompteRaisonSociale()
 * @method string setCompteRaisonSociale()
 * @method string getCompteCommune()
 * @method string setCompteCommune()
 * @method string getCompteAdresse()
 * @method string setCompteAdresse()
 * @method string getCompteCodePostal()
 * @method string setCompteCodePostal()
 * @method string getCompteLon()
 * @method string setCompteLon()
 * @method string getCompteLat()
 * @method string setCompteLat()
 * @method string getCompteTelephoneBureau()
 * @method string setCompteTelephoneBureau()
 * @method string getCompteTelephonePrive()
 * @method string setCompteTelephonePrive()
 * @method string getCompteTelephoneMobile()
 * @method string setCompteTelephoneMobile()
 * @method string getCompteEmail()
 * @method string setCompteEmail()
 * @method string getTypeRendezvous()
 * @method string setTypeRendezvous()
 * @method string getRendezvousCommentaire()
 * @method string setRendezvousCommentaire()
 * @method string getNomAgentOrigine()
 * @method string setNomAgentOrigine()
 * @method string getConstat()
 * @method string setConstat()
 * @method string getPosition()
 * @method string setPosition()
 
 */

abstract class BaseTourneeRendezVous extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Tournee';
       $this->_tree_class_name = 'TourneeRendezVous';
    }
                
}