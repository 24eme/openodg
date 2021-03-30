<?php
class ExportTransactionPDF extends ExportDeclarationLotsPDF {

  protected function getHeaderTitle() {
      $date = new DateTime($this->declarationLot->date);
      $titre = sprintf("Déclaration de Vrac export du %s", $date->format('d/m/Y'));
      return $titre;
  }

  public function create() {
      @$this->printable_document->addPage($this->getPartial('transaction/pdf', array('document' => $this->declarationLot, 'etablissement' => $this->etablissement)));
  }
}
