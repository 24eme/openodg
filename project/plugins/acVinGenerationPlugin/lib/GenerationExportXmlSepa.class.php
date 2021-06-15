<?php

class GenerationExportXmlSepa extends GenerationAbstract
{
    public function generate() {

      $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS);
      $sepa_file = "generation/".$this->generation->date_emission."_factures_sepa.xml";

      $date_facturation = $this->generation->arguments->exist('date_facturation') ? Date::getIsoDateFromFrenchDate($this->generation->arguments->get('date_facturation')) : null;

      $handle_factures = fopen(sfConfig::get('sf_web_dir')."/".$sepa_file,'a');

      if(!class_exists("ExportXMLSEPA")){
          throw new \Exception("La classe ExportXMLSEPA n'existe pas");
      }

      //on parcours notre array de facture et on appelle la fonction getXml():
      $sepa = ExportXMlSEPA::getExportXMLSepaForCurrentPrelevements(true);
      $xml = $sepa->getXml();
      $sepa->saveExportedSepa();

      $this->generation->documents = $sepa->getFacturesId();  

      // faire le document
      fwrite($handle_factures, $xml);
      fclose($handle_factures);


      //on sauve notre fichier;
      $this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);
      $this->generation->save();

    }

    public function getDocumentName() {

        return 'ExportXMLSEPA';
    }

}
