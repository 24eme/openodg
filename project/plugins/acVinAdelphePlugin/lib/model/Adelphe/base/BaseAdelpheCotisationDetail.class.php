<?php
class BaseAdelpheCotisationDetail extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'Adelphe';
       $this->_tree_class_name = 'AdelpheCotisationDetail';
    }

}
