<?php


abstract class BaseTransaction extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Transaction';
    }

}
