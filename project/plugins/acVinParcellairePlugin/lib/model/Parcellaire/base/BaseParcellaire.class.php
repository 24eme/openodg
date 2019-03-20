<?php
/**
 * BaseParcellaire
 * 
 * Base model for Parcellaire
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $date
 * @property string $identifiant
 * @property string $source
 * @property ParcellaireDeclarant $declarant
 * @property ParcellaireDeclaration $declaration
 * @property acCouchdbJson $pieces

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method string getDate()
 * @method string setDate()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getSource()
 * @method string setSource()
 * @method ParcellaireDeclarant getDeclarant()
 * @method ParcellaireDeclarant setDeclarant()
 * @method ParcellaireDeclaration getDeclaration()
 * @method ParcellaireDeclaration setDeclaration()
 * @method acCouchdbJson getPieces()
 * @method acCouchdbJson setPieces()
 
 */
 
abstract class BaseParcellaire extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Parcellaire';
    }
    
}