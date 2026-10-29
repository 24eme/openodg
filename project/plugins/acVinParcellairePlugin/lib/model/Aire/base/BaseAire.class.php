<?php
/**
 * BaseAire
 * 
 * Base model for Aire
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $date_import
 * @property string $commune_identifiant
 * @property string $commune_libelle
 * @property string $denomination_identifiant
 * @property string $denomination_libelle
 * @property string $geojson
 * @property string $md5

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method string getDateImport()
 * @method string setDateImport()
 * @method string getCommuneIdentifiant()
 * @method string setCommuneIdentifiant()
 * @method string getCommuneLibelle()
 * @method string setCommuneLibelle()
 * @method string getDenominationIdentifiant()
 * @method string setDenominationIdentifiant()
 * @method string getDenominationLibelle()
 * @method string setDenominationLibelle()
 * @method string getGeojson()
 * @method string setGeojson()
 * @method string getMd5()
 * @method string setMd5()
 
 */
 
abstract class BaseAire extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Aire';
    }
    
}