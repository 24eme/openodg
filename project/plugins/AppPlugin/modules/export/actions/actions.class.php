<?php

class exportActions extends sfActions {

    public function executeIndex(sfWebRequest $request)
    {
        $this->generationsList = array_merge(
            GenerationClient::getInstance()->findHistoryWithType(GenerationClient::TYPE_DOCUMENT_EXPORT_CSV, 100),
            GenerationClient::getInstance()->findHistoryWithType(GenerationClient::TYPE_DOCUMENT_EXPORT_SAGE, 100)
        );

        uasort($this->generationsList, "GenerationClient::sortHistory");

        $generations = array();

        foreach(DeclarationClient::getInstance()->getTypesAndCampagneForExport() as $typeCampagne) {
            $generation = new Generation();
            $generation->type_document = GenerationClient::TYPE_DOCUMENT_EXPORT_CSV;
            $generation->libelle = sprintf("Export CSV %s %s", $typeCampagne->type, $typeCampagne->campagne);
            $generation->arguments = array("campagne" => $typeCampagne->campagne, "type_document" => $typeCampagne->type);
            $generations[$typeCampagne->campagne."_".$generation->type."_".implode($generation->arguments->toArray(true, false), "_")] = $generation;
        }

        $generation = new Generation();
        $generation->type_document = GenerationClient::TYPE_DOCUMENT_EXPORT_SAGE;
        $generation->libelle = sprintf("Export SAGE");
        $generation->arguments = array();

        $generations[GenerationClient::TYPE_DOCUMENT_EXPORT_SAGE] = $generation;

        krsort($generations);

        $this->form = new ExportGenerationForm($generations);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $generation = $this->form->getGeneration();
        $generation->save();

        return $this->redirect('generation_view', array('type_document' => $generation->type_document, 'date_emission' => $generation->date_emission));
    }

}
