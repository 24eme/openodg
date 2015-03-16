<?php

class degustationComponents extends sfComponents {

    public function executeList(sfWebRequest $request) {

        $this->degustations = DegustationClient::getInstance()->getDegustations();
    }

}
