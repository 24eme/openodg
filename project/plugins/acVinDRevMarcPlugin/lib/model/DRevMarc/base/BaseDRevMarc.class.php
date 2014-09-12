<?php
/**
 * BaseDRevMarc
 * 
 * Base model for DRevMarc
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type

 * @method string get_id()
 * @method string set_id()
 * @method string get_rev()
 * @method string set_rev()
 * @method string getType()
 * @method string setType()
 
 */
 
abstract class BaseDRevMarc extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'DRevMarc';
    }
    
}