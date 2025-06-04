<?php
class generationActions extends sfActions {

  private function getGenerationFromRequest(sfWebRequest $request) {
      $this->generation = GenerationClient::getInstance()->find($request['id']);
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

    public function executeList(sfWebRequest $request)
    {
        $this->generations = GenerationClient::getInstance()->findHistoryWithType(GenerationClient::TYPE_DOCUMENT_SHELL, 100);

        $this->tasks = [];
        $scripts = [];
        foreach(glob(sfConfig::get('sf_root_dir').'/bin/tasks/global/*.sh') as $script) {
            $scripts[] = $script;
        }
        foreach(glob(sfConfig::get('sf_root_dir').'/bin/tasks/'.sfConfig::get('sf_app').'/*.sh') as $script) {
            $scripts[] = $script;
        }
        foreach($scripts as $script) {
            $content = fopen($script, 'r');

            $title = $desc = '';
            $id = basename($script);

            while ($line = fgets($content)) {
                if (strpos($line, '# Title') === 0) {
                    $title = str_replace(': ', '', strpbrk($line, ':'));
                }
                if (strpos($line, '# Description') === 0) {
                    $desc = str_replace(': ', '', strpbrk($line, ':'));
                }
            }

            $this->tasks[$id] = compact('title', 'desc', 'script');
        }

        if (! $request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        $task = new Generation();
        $task->type_document = GenerationClient::TYPE_DOCUMENT_SHELL;
        $task->libelle = $this->tasks[$request->getParameter('task')]['title'];
        $task->arguments = [
            'bash',
            $this->tasks[$request->getParameter('task')]['script'],
            sfConfig::get('sf_app'),
        ];
        $task->save();
        return $this->redirect('generation_view', ['id' => $task->_id]);
    }

  public function executeReload(sfWebRequest $request) {
      $generation = $this->getGenerationFromRequest($request);
      $generation->reload();
      $generation->save();

      return $this->redirect('generation_view', ['id' => $generation->_id]);
  }

}
