<?php
/**
 * BaseTourneeAgent
 * 
 * Base model for TourneeAgent

 * @property string $nom
 * @property string $email
 * @property string $adresse
 * @property string $commune
 * @property string $code_postal
 * @property string $lat
 * @property string $lon
 * @property acCouchdbJson $dates

 * @method string getNom()
 * @method string setNom()
 * @method string getEmail()
 * @method string setEmail()
 * @method string getAdresse()
 * @method string setAdresse()
 * @method string getCommune()
 * @method string setCommune()
 * @method string getCodePostal()
 * @method string setCodePostal()
 * @method string getLat()
 * @method string setLat()
 * @method string getLon()
 * @method string setLon()
 * @method acCouchdbJson getDates()
 * @method acCouchdbJson setDates()
 
 */

abstract class BaseTourneeAgent extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Tournee';
       $this->_tree_class_name = 'TourneeAgent';
    }
                
}