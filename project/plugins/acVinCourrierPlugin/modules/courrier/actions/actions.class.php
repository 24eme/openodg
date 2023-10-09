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

        $this->form->save();
        return $this->redirect('degustation_lot_historique', array('identifiant' => $this->etablissement->identifiant, 'unique_id' => $this->lot->unique_id));
    }
}
