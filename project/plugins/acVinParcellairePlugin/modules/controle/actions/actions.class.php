<?php
class controleActions extends sfActions
{
    public function executeNouveau(sfWebRequest $request)
    {
    	$this->etablissement = $this->getRoute()->getEtablissement();

        if(!$this->getUser()->isAdmin()) {
            throw new sfError403Exception("Accès admin uniquement");
        }
		$this->periode = $request->getParameter('periode');
        $this->controle = ControleClient::getInstance()->findOrCreate($this->etablissement->identifiant);
        $this->controle->save();

        return $this->redirect('controle_parcelles', array('id' => $this->controle->_id));
    }

    public function executeParcelles(sfWebRequest $request)
    {
    	$this->controle = $this->getRoute()->getControle();

        if(!$this->getUser()->isAdmin()) {
            throw new sfError403Exception("Accès admin uniquement");
        }

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->controle->updateParcelles($request->getPostParameter('parcelles', []));
            $this->controle->save();
            return $this->redirect('controle_parcelles', array('id' => $this->controle->_id));
        }
    }
}
