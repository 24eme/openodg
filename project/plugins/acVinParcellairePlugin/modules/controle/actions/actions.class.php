<?php
class controleActions extends sfActions
{
    public function executeIndex(sfWebRequest $request)
    {
        $this->controles = ControleClient::getInstance()->findAllByStatus();
        $this->stats = [];
        foreach ($this->controles as $statut => $controles) {
            foreach($controles as $c) {
                if (!isset($this->stats[$statut][$c->date_tournee])) {
                    $this->stats[$statut][$c->date_tournee] = [
                        'parcelles' => [],
                        'operateurs' => [],
                        'date_tournee' => $c->date_tournee,
                        'type_tournee' => $c->type_tournee
                    ];
                }
                $this->stats[$statut][$c->date_tournee]['parcelles'] += $c->parcelles->toArray(true,false);
                $this->stats[$statut][$c->date_tournee]['operateurs'][$c->identifiant] = $c->declarant->nom;
            }
        }
    }

    public function executeNouveau(sfWebRequest $request)
    {
    	$this->etablissement = $this->getRoute()->getEtablissement();

        if(!$this->getUser()->isAdmin()) {
            throw new sfError403Exception("Accès admin uniquement");
        }
        $this->controle = ControleClient::getInstance()->findOrCreate($this->etablissement->identifiant);
        $this->controle->save();

        return $this->redirect('controle_index');
    }

    public function executeParcelles(sfWebRequest $request)
    {
    	$this->controle = $this->getRoute()->getControle();

        if(!$this->getUser()->isAdmin()) {
            throw new sfError403Exception("Accès admin uniquement");
        }

        $this->parcellaire = $this->controle->getParcellaire();

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->controle->updateParcelles($request->getPostParameter('parcelles', []));
            $this->controle->save();
            return $this->redirect('controle_index');
        }
    }

    private function getControlesByDateTournee($dateTournee)
    {
        $controles = [];
        foreach (ControleClient::getInstance()->findAll() as $controle) {
            if ($dateTournee == $controle->date_tournee) {
                $controles[$controle->_id] = $controle->getDataToDump();
            }
        }
        return $controles;
    }

    public function executeAppOrga(sfWebRequest $request)
    {
        $this->json = json_encode($this->getControlesByDateTournee($request->getParameter('date')), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        $this->setLayout('appLayout');
    }

    public function executeAppTerrain(sfWebRequest $request)
    {
        $this->json = json_encode($this->getControlesByDateTournee($request->getParameter('date')), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        $this->points_de_controle = json_encode(ControleConfiguration::getInstance()->getPointsDeControle(), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        $this->setLayout('appLayout');
    }

    public function executeAppOrgaSave(sfWebRequest $request) {
        if (!$request->isMethod(sfWebRequest::POST)) {
            throw new sfError403Exception();
        }
        $date_tournee = $request->getParameter('date');
        $data = json_decode($request->getParameter('data'));
        foreach ($data as $controleId => $parcellesIds) {
            if ($controle = ControleClient::getInstance()->find($controleId)) {

                $controle->updateParcelles($parcellesIds);
                $controle->save();
            }
        }
        return $this->redirect('controle_index');
    }

    public function executeSetDateTournee(sfWebRequest $request)
    {
        $this->controle = $this->getRoute()->getControle();
        if (!$request->getParameter('date_tournee')) {
            return sfView::SUCCESS;
        }
        $this->controle->date_tournee = $request->getParameter('date_tournee');
        $this->controle->type_tournee = $request->getParameter('type_tournee');
        $this->controle->save();
        return $this->redirect('controle_index');
    }

    public function executeVisualisation(sfWebRequest $request)
    {
        $this->controle = $this->getRoute()->getControle();
    }

    public function executeListeOperateursTournee(sfWebRequest $request)
    {
        $this->controles = $this->getControlesPlanifies($request->getParameter('date'))[$request->getParameter('date')]['controles'];
    }

    public function executeListeManquementsControle(sfWebRequest $request)
    {
        $this->controle = ControleClient::getInstance()->find($request->getParameter('id'));
        $this->listeManquements = $this->controle->getManquementsListe();
        if (! $this->controle->hasManquementTerrain() && $this->controle->hasConstatTerrain()) {
            $this->redirect('controle_update_manquements', array('id' => $this->controle->_id));
        }
        $this->form = new ControleManquementsForm($this->controle);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));

            if (! $this->form->isValid()) {
                return sfView::SUCCESS;
            }

            $this->form->save();
            return $this->redirect('controle_liste_manquements_controle', array('id' => $this->controle->_id));
        }
    }

    public function executeUpdateManquements(sfWebRequest $request)
    {
        $controle = ControleClient::getInstance()->find($request->getParameter('id'));
        $controle->generateManquements();
        $controle->save();
        return $this->redirect('controle_liste_manquements_controle', array('id' => $controle->_id));
    }

    public function executeTransmissionData(sfWebRequest $request)
    {
        if ($request->isMethod(sfWebRequest::POST)) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            $controleBase = ControleClient::getInstance()->find($data['controle']['_id']);
            $controleBase->updateParcellePointsControleFromJson($data);
            exit;
        }
    }

    public function executeListeAjoutManquementsControle(sfWebRequest $request)
    {
        $this->controle = ControleClient::getInstance()->find($request->getParameter('id'));
        $this->listeManquements = ControleConfiguration::getInstance()->getAllLibellesManquements();
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->controle->addManquementDocumentaire($_POST['manquement']);
            $this->controle->save();
            return $this->redirect('controle_liste_manquements_controle', array('id' => $this->controle->_id));
        }
    }
}
