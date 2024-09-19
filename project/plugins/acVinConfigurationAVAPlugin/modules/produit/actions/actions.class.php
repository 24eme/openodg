<?php

/**
 * produit actions.
 *
 * @package    declarvin
 * @subpackage produit
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class produitActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
      if(!$request->getParameter('date')) {

        return $this->redirect('produits', array('date' => date('Y-m-d')));
      }
      set_time_limit(0);

      $this->organisme = Organisme::getInstance(null, Organisme::DEGUSTATION_TYPE);
      $this->date = $request->getParameter('date');
      $this->config = ConfigurationClient::getConfiguration($this->date);
      $this->produits = $this->config->declaration->getProduits($this->date);
      $this->notDisplayDroit = true;
  }

}
