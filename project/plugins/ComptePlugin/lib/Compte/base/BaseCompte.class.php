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
 * @property string $type_compte
 * @property string $civilite
 * @property string $nom
 * @property string $prenom
 * @property string $nom_a_afficher
 * @property string $adresse
 * @property string $code_postal
 * @property string $ville
 * @property string $telephone_bureau
 * @property string $telephone_prive
 * @property string $telephone_mobile
 * @property string $fax
 * @property string $email
 * @property string $siret
 * @property string $cvi
 * @property string $etablissement
 * @property acCouchdbJson $tags

 * @method string get_id()
 * @method string set_id()
 * @method string get_rev()
 * @method string set_rev()
 * @method string getType()
 * @method string setType()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getTypeCompte()
 * @method string setTypeCompte()
 * @method string getCivilite()
 * @method string setCivilite()
 * @method string getNom()
 * @method string setNom()
 * @method string getPrenom()
 * @method string setPrenom()
 * @method string getNomAAfficher()
 * @method string setNomAAfficher()
 * @method string getAdresse()
 * @method string setAdresse()
 * @method string getCodePostal()
 * @method string setCodePostal()
 * @method string getVille()
 * @method string setVille()
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
 * @method string getSiret()
 * @method string setSiret()
 * @method string getCvi()
 * @method string setCvi()
 * @method string getEtablissement()
 * @method string setEtablissement()
 * @method acCouchdbJson getTags()
 * @method acCouchdbJson setTags()
 
 */
 
abstract class BaseCompte extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Compte';
    }
    
}