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
 * @property string $email
 * @property string $telephone
 * @property string $fax
 * @property acCouchdbJson $siege

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
 * @method string getEmail()
 * @method string setEmail()
 * @method string getTelephone()
 * @method string setTelephone()
 * @method string getFax()
 * @method string setFax()
 * @method acCouchdbJson getSiege()
 * @method acCouchdbJson setSiege()
 
 */
 
abstract class BaseEtablissement extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Etablissement';
    }
    
}