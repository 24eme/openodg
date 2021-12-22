<?php
class ExportTransactionPDF extends ExportPDF {
    protected $declaration = null;
    protected $etablissement = null;

    public function __construct($declaration, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null)
    {
        $this->declaration = $declaration;
        $this->etablissement = $declaration->getEtablissementObject();
        $app = strtoupper(sfConfig::get('sf_app'));

        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
    }


  protected function getHeaderTitle() {
      $date = new DateTime($this->declaration->date);
      $titre = sprintf("Déclaration de Vrac export du %s", $date->format('d/m/Y'));
      return $titre;
  }
    protected function getHeaderSubtitle()
    {
    }

  public function create() {
      @$this->printable_document->addPage($this->getPartial('transaction/pdf', array('document' => $this->declaration, 'etablissement' => $this->etablissement)));
  }

    protected function getConfig() {
        return new ExportTransactionPDFConfig();
    }
}
