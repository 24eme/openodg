<?php
/**
 * BaseDegustationNote
 * 
 * Base model for DegustationNote

 * @property string $note
 * @property acCouchdbJson $defauts

 * @method string getNote()
 * @method string setNote()
 * @method acCouchdbJson getDefauts()
 * @method acCouchdbJson setDefauts()
 
 */

abstract class BaseDegustationNote extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Degustation';
       $this->_tree_class_name = 'DegustationNote';
    }
                
}