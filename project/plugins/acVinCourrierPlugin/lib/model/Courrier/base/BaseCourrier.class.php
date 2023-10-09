<?php


abstract class BaseCourrier extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Courrier';
    }

}
