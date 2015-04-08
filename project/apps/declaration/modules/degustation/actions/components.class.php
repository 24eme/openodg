<?php

class degustationComponents extends sfComponents {

    public function executeList(sfWebRequest $request) {

        $this->tournees = TourneeClient::getInstance()->getTournees();
    }

}
