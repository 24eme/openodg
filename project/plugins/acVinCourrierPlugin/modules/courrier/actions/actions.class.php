<?php

/**
 * produit actions.
 *
 * @package    declarvin
 * @subpackage produit
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class courrierActions extends sfActions
{
    function executeCreate(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $unique_id = $request->getParameter('lot_unique_id');
        $this->lot = LotsClient::getInstance()->findByUniqueId($this->etablissement->getIdentifiant(), $unique_id);
        $this->form = new CourrierNouveauForm($this->etablissement, $this->lot);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $courrier = $this->form->save();
        return $this->redirect('courrier_extras', array('identifiant' => $this->etablissement->identifiant, 'unique_id' => $this->lot->unique_id, 'id_form' => $courrier->_id));
    }

    function executeExtras(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $unique_id = $request->getParameter('unique_id');
        $this->lot = LotsClient::getInstance()->findByUniqueId($this->etablissement->getIdentifiant(), $unique_id);
        $this->courrier = CourrierClient::getInstance()->find($request->getParameter('id_form'));

        $this->form = new CourrierExtrasNouveauForm($this->courrier);
        if (!$this->form->getNbFields()) {
            return $this->redirect('degustation_lot_historique', array('identifiant' => $this->etablissement->identifiant, 'unique_id' => $this->lot->unique_id));
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();
        return $this->redirect('degustation_lot_historique', array('identifiant' => $this->etablissement->identifiant, 'unique_id' => $this->lot->unique_id));
    }

    function executeVisualisation(sfWebRequest $request) {
        $courrier = CourrierClient::getInstance()->find($request->getParameter('id'));
        if ($courrier->exist('_attachments')) {
            foreach($courrier->_attachments as $id => $a) {
                if (!strpos($a->content_type, 'pdf')) {
                    continue;
                }
                $file_content = file_get_contents($courrier->getAttachmentUri($id));
                $this->getResponse()->setHttpHeader('Content-Type', 'application/pdf');
                $this->getResponse()->setHttpHeader('Content-disposition', 'attachment; filename="' . basename($id) . '"');
                $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
                $this->getResponse()->setHttpHeader('Content-Length', strlen($file_content));
                $this->getResponse()->setHttpHeader('Pragma', '');
                $this->getResponse()->setHttpHeader('Cache-Control', 'public');
                $this->getResponse()->setHttpHeader('Expires', '0');
                return $this->renderText($file_content);
            }
        }
        $this->document = new ExportDegustationCourrierPDF($courrier, $request->getParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));
        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }
        $this->document->generate();
        $this->document->addHeaders($this->getResponse());
        return $this->renderText($this->document->output());

    }

    public function executeRedeguster(sfWebRequest $request)
    {
        $courrier = CourrierClient::getInstance()->find($request->getParameter('identifiant'));
        $lot = $courrier->getLot($request->getParameter('lot'));

        $lot->redegustation();

        $courrier->generateMouvementsLots();
        $courrier->save();

        return $this->redirect('degustation_lot_historique', ['identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id]);
    }

    public function executeRecoursOc(sfWebRequest $request)
    {
        if (RegionConfiguration::getInstance()->hasOC() && Organisme::getInstance()->isOC() === false) {
            throw new sfException('Vous ne pouvez pas faire de recours OC');
        }

        $courrier = CourrierClient::getInstance()->find($request->getParameter('identifiant'));
        $lot = $courrier->getLot($request->getParameter('lot'));

        $lot->recoursOc();

        $courrier->generateMouvementsLots();
        $courrier->save();

        return $this->redirect('degustation_lot_historique', ['identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id]);
    }
}
