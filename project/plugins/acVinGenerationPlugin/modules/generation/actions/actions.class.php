<?php
class generationActions extends sfActions {

  private function getGenerationFromRequest(sfWebRequest $request) {
      $this->type = $request['type_document'];
      $this->identifiant = isset($request['identifiant'])? $request['identifiant'] : null;
      $this->nom = ($this->identifiant)? EtablissementClient::getInstance()->retrieveById($this->identifiant)->nom : null;
      $this->date_emission = $request['date_emission'];
      $this->generation = GenerationClient::getInstance()->find(GenerationClient::getInstance()->getId($this->type, $this->date_emission));
      $this->forward404Unless($this->generation);

      return $this->generation;
  }

  public function executeView(sfWebRequest $request) {
      $this->generation = $this->getGenerationFromRequest($request);
      $this->retour = $request->getParameter('retour',false);
      if($this->generation->type_document == GenerationClient::TYPE_DOCUMENT_FACTURES) {
          $this->menuActive = 'facturation';
          $this->backUrl = ($this->retour)? $this->retour : $this->generateUrl('facturation');
      }

      if($this->generation->type_document == GenerationClient::TYPE_DOCUMENT_EXPORT_CSV) {
          $this->menuActive = 'export';
          $this->backUrl = $this->generateUrl('export');
      }

      if($this->generation->type_document == GenerationClient::TYPE_DOCUMENT_EXPORT_SAGE) {
          $this->menuActive = 'export';
          $this->backUrl = $this->generateUrl('export');
      }

      if($this->generation->type_document == GenerationClient::TYPE_DOCUMENT_EXPORT_PARCELLAIRE) {
          $this->menuActive = 'export';
          $this->backUrl = $this->generateUrl('export');
      }

      $this->sous_generations_conf = [];
      if ($this->generation->statut === GenerationClient::GENERATION_STATUT_GENERE &&
          GenerationConfiguration::getInstance()->hasSousGeneration($this->generation->type_document))
      {
          $this->sous_generations_conf = GenerationConfiguration::getInstance()->getSousGeneration($this->type_generation);
      }
      $this->sous_generations = $this->generation->getSubGenerations();
  }

  public function executeList(sfWebRequest $request) {
      $this->type = $request['type_document'];
      $this->historyGeneration = GenerationClient::getInstance()->findHistoryWithType($this->type);
  }

  public function executeReload(sfWebRequest $request) {
      $generation = $this->getGenerationFromRequest($request);
      $generation->reload();
      $generation->save();

      return $this->redirect('generation_view', ['id' => $generation->_id]);
  }

}
