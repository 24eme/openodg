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

    public function getFileName($with_rev = false)
    {
        return self::buildFileName($this->declaration, true);
    }

    public static function buildFileName($declaration, $with_rev = false)
    {
        $filename = sprintf("_%s", $declaration->_id);

        if ($with_rev) {
            $filename .= '_' . $declaration->_rev;
        }

        return $filename . '.pdf';
    }


  protected function getHeaderTitle() {
      $date = new DateTime($this->declaration->date);
      $titre = sprintf("Déclaration de ".TransactionConfiguration::getInstance()->getDeclarationName()." du %s", $date->format('d/m/Y'));
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
