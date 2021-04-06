<?php
class ExportConditionnementPDF extends ExportDeclarationLotsPDF {

  protected function getHeaderTitle() {
      $date = new DateTime($this->declarationLot->date);
      $titre = sprintf("Déclaration de Conditionnement du %s", $date->format('d/m/Y'));
      return $titre;
  }

  public function create() {
      @$this->printable_document->addPage($this->getPartial('conditionnement/pdf', array('document' => $this->declarationLot, 'etablissement' => $this->etablissement)));
  }
}
