<?php
class ExportConditionnementPDF extends ExportDeclarationLotsPDF {

    protected $declaration = null;
    protected $etablissement = null;

    public function __construct($declaration, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->declaration = $declaration;
        $this->etablissement = $declaration->getEtablissementObject();

        parent::__construct($declaration, $type, $use_cache, $file_dir, $filename);
    }

  protected function getHeaderTitle() {
      $date = new DateTime($this->declaration->date);
      $titre = sprintf("Déclaration de Conditionnement du %s", $date->format('d/m/Y'));
      return $titre;
  }

  protected function getHeaderSubtitle() {
      $header_subtitle = sprintf("%s\n\n", $this->declaration->declarant->nom);
      if (!$this->declaration->isPapier() && $this->declaration->validation && $this->declaration->validation !== true) {
          $date = new DateTime($this->declaration->validation);
          $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
          if($this->declaration->validation_odg) {
              $dateOdg = new DateTime($this->declaration->validation_odg);
              $header_subtitle .= ", validée par l'ODG le ".$dateOdg->format('d/m/Y');
          } else {
              $header_subtitle .= ", en attente de l'approbation par l'ODG";
          }

      } elseif(!$this->declaration->isPapier()) {
          $header_subtitle .= sprintf("Exemplaire brouillon");
      }

      if ($this->declaration->isPapier() && $this->declaration->validation && $this->declaration->validation !== true) {
          $date = new DateTime($this->declaration->validation);
          $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
      }
      return $header_subtitle;
  }

  public function create() {
      @$this->printable_document->addPage($this->getPartial('conditionnement/pdf', array('document' => $this->declaration, 'etablissement' => $this->etablissement)));
  }

  protected function getConfig() {
      $config = new ExportDeclarationLotsPDFConfig();
      $config->orientation= ExportDeclarationLotsPDFConfig::ORIENTATION_LANDSCAPE;
      return $config;
  }
}
