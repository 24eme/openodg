<?php

class authActions extends sfActions
{
   
    public function executeLogin(sfWebRequest $request)
    {
        $this->form = new LoginForm();

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {
            
            return sfView::SUCCESS;
        }

        $this->getUser()->signIn($this->form->getValue('etablissement'));

        return $this->redirect('drev_index');
    }
}
