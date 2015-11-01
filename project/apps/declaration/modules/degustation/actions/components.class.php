<?php

class degustationComponents extends sfComponents {

    public function executeList(sfWebRequest $request) {

        $tournees = TourneeClient::getInstance()->getTournees();
        $this->tournees = array();
        foreach($tournees as $tournee) {
            if(!$tournee->appellation) {
                continue;
            }

            $this->tournees[$tournee->_id] = $tournee;
        }
    }

}
