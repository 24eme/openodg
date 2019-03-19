<?php
/**
 * BaseCompte
 * 
 * Base model for Compte
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $identifiant
 * @property string $identifiant_interne
 * @property string $type_compte
 * @property string $code_insee
 * @property string $civilite
 * @property string $nom
 * @property string $prenom
 * @property string $raison_sociale
 * @property string $nom_a_afficher
 * @property string $adresse
 * @property string $adresse_complement_destinataire
 * @property string $adresse_complement_lieu
 * @property string $code_postal
 * @property string $commune
 * @property string $cedex
 * @property string $pays
 * @property string $telephone_bureau
 * @property string $telephone_prive
 * @property string $telephone_mobile
 * @property string $fax
 * @property string $email
 * @property string $web
 * @property string $siret
 * @property string $siren
 * @property string $cvi
 * @property string $etablissement
 * @property string $commentaires
 * @property string $statut
 * @property string $numero_archive
 * @property string $campagne_archive
 * @property string $date_archivage
 * @property string $date_creation
 * @property string $no_accises
 * @property string $lat
 * @property string $lon
 * @property acCouchdbJson $droits
 * @property acCouchdbJson $chais
 * @property acCouchdbJson $formations
 * @property acCouchdbJson $infos
 * @property acCouchdbJson $tags

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getIdentifiantInterne()
 * @method string setIdentifiantInterne()
 * @method string getTypeCompte()
 * @method string setTypeCompte()
 * @method string getCodeInsee()
 * @method string setCodeInsee()
 * @method string getCivilite()
 * @method string setCivilite()
 * @method string getNom()
 * @method string setNom()
 * @method string getPrenom()
 * @method string setPrenom()
 * @method string getRaisonSociale()
 * @method string setRaisonSociale()
 * @method string getNomAAfficher()
 * @method string setNomAAfficher()
 * @method string getAdresse()
 * @method string setAdresse()
 * @method string getAdresseComplementDestinataire()
 * @method string setAdresseComplementDestinataire()
 * @method string getAdresseComplementLieu()
 * @method string setAdresseComplementLieu()
 * @method string getCodePostal()
 * @method string setCodePostal()
 * @method string getCommune()
 * @method string setCommune()
 * @method string getCedex()
 * @method string setCedex()
 * @method string getPays()
 * @method string setPays()
 * @method string getTelephoneBureau()
 * @method string setTelephoneBureau()
 * @method string getTelephonePrive()
 * @method string setTelephonePrive()
 * @method string getTelephoneMobile()
 * @method string setTelephoneMobile()
 * @method string getFax()
 * @method string setFax()
 * @method string getEmail()
 * @method string setEmail()
 * @method string getWeb()
 * @method string setWeb()
 * @method string getSiret()
 * @method string setSiret()
 * @method string getSiren()
 * @method string setSiren()
 * @method string getCvi()
 * @method string setCvi()
 * @method string getEtablissement()
 * @method string setEtablissement()
 * @method string getCommentaires()
 * @method string setCommentaires()
 * @method string getStatut()
 * @method string setStatut()
 * @method string getNumeroArchive()
 * @method string setNumeroArchive()
 * @method string getCampagneArchive()
 * @method string setCampagneArchive()
 * @method string getDateArchivage()
 * @method string setDateArchivage()
 * @method string getDateCreation()
 * @method string setDateCreation()
 * @method string getNoAccises()
 * @method string setNoAccises()
 * @method string getLat()
 * @method string setLat()
 * @method string getLon()
 * @method string setLon()
 * @method acCouchdbJson getDroits()
 * @method acCouchdbJson setDroits()
 * @method acCouchdbJson getChais()
 * @method acCouchdbJson setChais()
 * @method acCouchdbJson getFormations()
 * @method acCouchdbJson setFormations()
 * @method acCouchdbJson getInfos()
 * @method acCouchdbJson setInfos()
 * @method acCouchdbJson getTags()
 * @method acCouchdbJson setTags()
 
 */
 
abstract class BaseCompte extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Compte';
    }
    
}