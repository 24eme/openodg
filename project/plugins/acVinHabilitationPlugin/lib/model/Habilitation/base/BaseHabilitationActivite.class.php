<?php
/**
 * BaseHabilitationActivite
 *
 * Base model for HabilitationActivite



 */

abstract class BaseHabilitationActivite extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationActivite';
    }

}
