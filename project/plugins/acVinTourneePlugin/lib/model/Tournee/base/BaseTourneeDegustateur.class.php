<?php
/**
 * BaseTourneeDegustateur
 * 
 * Base model for TourneeDegustateur

 * @property string $nom
 * @property string $email
 * @property string $adresse
 * @property string $commune
 * @property string $code_postal
 * @property integer $presence

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
 * @method integer getPresence()
 * @method integer setPresence()
 
 */

abstract class BaseTourneeDegustateur extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Tournee';
       $this->_tree_class_name = 'TourneeDegustateur';
    }
                
}