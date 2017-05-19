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
 * @property acCouchdbJson $pieces

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
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
 * @method acCouchdbJson getPieces()
 * @method acCouchdbJson setPieces()
 
 */
 
abstract class BaseConstats extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Constats';
    }
    
}