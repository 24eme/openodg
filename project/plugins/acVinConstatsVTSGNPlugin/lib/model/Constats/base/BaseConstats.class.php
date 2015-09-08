<?php
/**
 * BaseConstats
 * 
 * Base model for Constats
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $identifiant
 * @property string $raison_sociale
 * @property string $campagne
 * @property string $cvi
 * @property string $adresse
 * @property string $commune
 * @property string $code_postal
 * @property string $email
 * @property string $lat
 * @property string $lon
 * @property acCouchdbJson $constats

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
 * @method string getCampagne()
 * @method string setCampagne()
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
 * @method acCouchdbJson getConstats()
 * @method acCouchdbJson setConstats()
 
 */
 
abstract class BaseConstats extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Constats';
    }
    
}