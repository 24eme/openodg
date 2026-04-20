<?php
class controleActions extends sfActions
{
    public function executeIndex(sfWebRequest $request)
    {
        $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
        $allControles = ControleClient::getInstance()->findAllByStatus();
        $this->tournees = [];
        $this->nbOperateur = count($allControles);
        foreach ($allControles as $statut => $controles) {
            if(!in_array($statut, [ControleClient::CONTROLE_STATUT_A_ORGANISER, ControleClient::CONTROLE_STATUT_ORGANISE, ControleClient::CONTROLE_STATUT_EN_MANQUEMENT])) {
                continue;
            }
            foreach($controles as $c) {
                $index = $c->date_tournee.'-'.$c->agent_identifiant;
                if (!isset($this->tournees[$index])) {
                    $this->tournees[$index] = [
                        'parcelles' => [],
                        'operateurs' => [],
                        'secteurs' => [],
                        'cooperatives' => [],
                        'date_tournee' => $c->date_tournee,
                        'agent' => $c->getAgent(),
                        'type_tournee' => $c->type_tournee,
                        'statut' => $statut
                    ];
                }
                $this->tournees[$index]['parcelles'] += $c->parcelles->toArray(true,false);
                $this->tournees[$index]['operateurs'][$c->identifiant] = $c->declarant->nom;
                $this->tournees[$index]['secteurs'][$c->secteur] = $c->secteur;
                foreach($c->liaisons_operateurs as $liaison) {
                    $this->tournees[$index]['cooperatives'][$liaison->id_etablissement] = "Coopérateurs pour " .$liaison->libelle_etablissement;
                }
            }
        }
        ksort($this->tournees);
    }

    public function executeOperateurs(sfWebRequest $request)
    {
        $this->controles = ControleClient::getInstance()->findAllByStatus();
    }

    public function executeEtablissementSelection(sfWebRequest $request) {
        $form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
        $form->bind($request->getParameter($form->getName()));
        if (!$form->isValid()) {

            return $this->redirect('controle_index');
        }

        return $this->redirect('controle_operateur', $form->getEtablissement());
    }


    public function executeNouveau(sfWebRequest $request)
    {
    	$this->etablissement = $this->getRoute()->getEtablissement();

        if(!$this->getUser()->isAdmin()) {
            throw new sfError403Exception("Accès admin uniquement");
        }
        $this->controle = ControleClient::getInstance()->findOrCreate($this->etablissement->identifiant);
        $type = $request->getParameter('type');
        if (in_array($type, ControleClient::getInstance()->getTypes())) {
            $this->controle->type_tournee = $type;
        }else{
            $this->controle->type_tournee = ControleClient::CONTROLE_TYPE_CONDITIONS;
        }
        $this->controle->save();

        return $this->redirect('controle_operateur', $this->etablissement);
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

    private function getControlesByDateTourneeAndAgentAndSetControle($dateTournee, $agentIdentifiant)
    {
        $controles = [];
        foreach (ControleClient::getInstance()->findAll() as $controle) {
            if ($dateTournee == $controle->date_tournee && $agentIdentifiant == $controle->agent_identifiant) {
                if (! $controle->getParcellaire() || ! count($controle->getParcellaire()->getParcelles()) ) {
                    continue;
                }
                $controles[$controle->_id] = $controle->getDataToDump();
            }
        }
        return $controles;
    }

    public function executeAppOrga(sfWebRequest $request)
    {
        $this->date_tournee = $request->getParameter('date');
        $this->agent_identifiant = $request->getParameter('agent_identifiant');
        $this->json = json_encode($this->getControlesByDateTourneeAndAgentAndSetControle($this->date_tournee, $this->agent_identifiant), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        $this->setLayout('appLayout');
    }

    public function executeAppTerrain(sfWebRequest $request)
    {
        $this->date_tournee = $request->getParameter('date');
        $this->agent_identifiant = $request->getParameter('agent_identifiant');
        $this->json = json_encode($this->getControlesByDateTourneeAndAgentAndSetControle($this->date_tournee, $this->agent_identifiant), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        $this->points_de_controle = json_encode(ControleConfiguration::getInstance()->getAllPointsDeControle(), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        $this->setLayout('appLayout');
    }

    public function executeAppOrgaSave(sfWebRequest $request) {
        if (!$request->isMethod(sfWebRequest::POST)) {
            throw new sfError403Exception();
        }
        $data = json_decode($request->getParameter('data'));
        foreach ($data as $controleId => $items) {
            if ($controle = ControleClient::getInstance()->find($controleId)) {
                $controle->heure_tournee = $items->heure_tournee;
                $controle->updateParcelles($items->parcelles);
                $controle->save();
            }
        }
        return $this->redirect('controle_index');
    }

    public function executeSetDateTournee(sfWebRequest $request)
    {
        $this->controle = $this->getRoute()->getControle();
        $this->agents = ControleClient::getAllAgents();
        if (!$request->getParameter('date_tournee')) {
            return sfView::SUCCESS;
        }
        $this->controle->date_tournee = $request->getParameter('date_tournee');
        $this->controle->type_tournee = $request->getParameter('type_tournee');
        $this->controle->agent_identifiant = $request->getParameter('agent_identifiant');
        $this->controle->save();
        return $this->redirect('controle_index');
    }

    public function executeVisualisation(sfWebRequest $request)
    {
        $this->controle = $this->getRoute()->getControle();
    }

    public function executeListeOperateursTournee(sfWebRequest $request)
    {
        $this->date_tournee = $request->getParameter('date');
        $this->agent_identifiant = $request->getParameter('agent_identifiant');
        $this->controles = ControleClient::getInstance()->findAllByDateTourneeAndAgent($this->date_tournee, $this->agent_identifiant);
    }

    public function executeListeManquementsControle(sfWebRequest $request)
    {
        $this->controle = ControleClient::getInstance()->find($request->getParameter('id'));
        $this->listeManquements = $this->controle->getManquementsListe();
        if (! $this->controle->hasManquementTerrain() && $this->controle->hasConstatTerrainActif()) {
            $this->redirect('controle_update_manquements', array('id' => $this->controle->_id));
        }
        $this->form = new ControleManquementsForm($this->controle);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));

            if (! $this->form->isValid()) {
                return sfView::SUCCESS;
            }
            $this->controle->manquements_valides = true;
            $this->form->save();
            return $this->redirect('controle_liste_operateur_tournee', array('date' => $this->controle->date_tournee, 'agent_identifiant' => $this->controle->agent_identifiant));
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
            $datas = json_decode($raw, true);

            $revApp = $datas['revision'];
            $controle = ControleClient::getInstance()->find($datas['idControle']);
            $idParcelle = $datas['idParcelle'] ?? null;
            $element = $datas['element'];

            if ($revApp != $controle->_rev) {
                $controle->logDifferenceRevision($revApp, $idParcelle, $raw);
                $reloadStatus = true;
            }
            $controle->updateControle($idParcelle, $element);
            $controle->save();

            $newRev = $controle->_rev;

            $response = [
                'success' => true,
                'message' => 'OK',
                'revision' => $newRev,
                'reloadStatus' => $reloadStatus
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    }

    public function executeListeAjoutManquementsControle(sfWebRequest $request)
    {
        $this->controle = ControleClient::getInstance()->find($request->getParameter('id'));
        $this->libellesConstats = ControleConfiguration::getInstance()->getAllLibellesConstats(false, $this->controle->type_tournee);
        $this->errors = [];
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->controle->addManquementManuel($_POST['manquement'], $POST['parcelles_id']);
            $this->controle->save();
            return $this->redirect('controle_liste_manquements_controle', array('id' => $this->controle->_id));
        }
    }

    public function executeManquementPdf(sfWebRequest $request)
    {
        $this->controle = ControleClient::getInstance()->find($request->getParameter('id'));
        $this->document = new ExportControleManquementPDF($this->controle, $this->controle->identifiant, $request->getParameter('output', 'pdf'), false);
        return $this->executePdf($request);
    }

    public function executeExportControlePdf(sfWebRequest $request)
    {
        $this->controle = ControleClient::getInstance()->find($request->getParameter('id'));
        $this->document = new ExportControlePDF($this->controle, $this->controle->identifiant, $request->getParameter('output', 'pdf'), false);
        return $this->executePdf($request);
    }

    public function executePDF(sfWebRequest $request) {
        set_time_limit(180);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    public function executeGestionManquements(sfWebRequest $request)
    {
        $this->sorted_controles = ControleClient::getInstance()->findByManquements();
    }

    public function executeListingManquementsOperateur(sfWebRequest $request)
    {
        $this->controle = ControleClient::getInstance()->find($request->getParameter('id_controle'));
        $this->sorted_manquements = $this->controle->getSortedManquementsActif();
    }

    public function executeClotureManquement(sfWebRequest $request)
    {
        $this->controle = ControleClient::getInstance()->find($request->getParameter('id_controle'));
        $this->controle->manquements[$request->getParameter('id_manquement')]->cloture_date = date('Y-m-d');
        $this->controle->manquements[$request->getParameter('id_manquement')]->cloture_type = $request->getParameter('type', ControleClient::CONTROLE_CLOTURE_LEVER);
        $this->controle->save();
        return $this->redirect('controle_liste_manquements_operateur', array('id_controle' => $this->controle->_id));
    }

    public function executeMailPrevisualisation(sfWebRequest $request)
    {
        $this->controle = ControleClient::getInstance()->find($request->getParameter('id_controle'));

        $this->date_tournee = $this->controle->date_tournee;
        $this->agent_identifiant = $this->controle->agent_identifiant;
        $this->controles = ControleClient::getInstance()->findAllByDateTourneeAndAgent($this->date_tournee, $this->agent_identifiant);

        $this->popup = true;

        $this->setTemplate('listeOperateursTournee');
    }

    public function executeSetEnvoiMail(sfWebRequest $request)
    {
        $this->controle = ControleClient::getInstance()->find($request->getParameter('id_controle'));
        $identifiant = $request->getParameter('identifiant');
        $mailto = $identifiant;
        $date = $request->getParameter('envoye', date('Y-m-d H:i:s'));

        if(!boolval($date)) {
            $date = null;
            $mailto = null;
        }

        $this->controle->setNotificationDateControleEtManquements($date);
        $this->controle->save();

        if ($mailto) {
            return $this->redirect('controle_liste_operateur_tournee', array('date' => $this->controle->date_tournee, 'agent_identifiant' => $this->controle->agent_identifiant, 'mail_to_identifiant' => $identifiant));
        } else {
            return $this->redirect('controle_liste_operateur_tournee', array('date' => $this->controle->date_tournee, 'agent_identifiant' => $this->controle->agent_identifiant));
        }
    }

    public function executeMailToNotification(sfWebRequest $request)
    {
        $this->controle = ControleClient::getInstance()->find($request->getParameter('id_controle'));
        $identifiant = $request->getParameter('identifiant');

        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Date', 'Partial'));


        $email = EtablissementClient::getInstance()->find($identifiant)->getEmail();
        $email = trim($email);

        $cc = Organisme::getInstance(null, 'degustation')->getEmail();
        if ($cc) {
            $cc = "cc=".$cc."&";
        }
        $subject = sprintf("Suite contrôle interne ODG %s", $this->controle->getDateFormat('Y'));
        $body = html_entity_decode(str_replace("\n", "%0A", strip_tags(get_partial('controle/notificationEmail', [
            'controle' => $this->controle,
            'identifiant' => $identifiant,
            'agent' => CompteClient::getInstance()->find($controle->agent_identifiant),
        ]))), ENT_QUOTES | ENT_XML1, 'UTF-8');

        $mailto = "mailto:$email?".$cc."subject=$subject&body=$body";
        $mailto = mb_strcut($mailto, 0, 1559); // Chrome limite les mailto à un certain nombre de caractères 1600 semblent être le max

        $this->getResponse()->clearHttpHeaders();
        $this->getResponse()->setStatusCode(302);
        $this->getResponse()->setHttpHeader('Location', $mailto);
        $this->getResponse()->setContent(sprintf('<html><head><meta http-equiv="refresh" content="%d;url=%s"/></head></html>', 0, $mailto));
        $this->getResponse()->send();

        throw new sfStopException();
    }

    public function executeOperateur(sfWebRequest $request)
    {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->form = new EtablissementChoiceForm(sfConfig::get('app_interpro', 'INTERPRO-declaration'), array('identifiant' => $this->etablissement->identifiant), true);
        $this->controles = ControleClient::getInstance()->findAllByIdentifiant($this->etablissement->identifiant);
        $this->manquements = ControleClient::getInstance()->findByManquements($this->etablissement->identifiant);
    }
}
