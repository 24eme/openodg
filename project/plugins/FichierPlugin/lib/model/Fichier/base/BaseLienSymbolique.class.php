<?php
/**
 * BaseLienSymbolique
 * 
 * Base model for LienSymbolique
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 
 */
 
abstract class BaseLienSymbolique extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'LienSymbolique';
    }
    
}