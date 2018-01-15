<?php

class globalActions extends sfActions {

    public function executeError404() {

    }

    public function executeError403() {

    }

    public function executeSetFlash(sfWebRequest $request) {

        $this->getUser()->setFlash($request->getParameter('type'), $request->getParameter('message'));

        return $this->redirect($request->getParameter('url'));
    }

}
