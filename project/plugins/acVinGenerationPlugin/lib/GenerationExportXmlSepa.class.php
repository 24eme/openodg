<?php

class GenerationExportXmlSepa extends GenerationAbstract
{
    public function generate() {

      $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS);
      $sepa_file = "generation/".$this->generation->date_emission."_factures_sepa.xml";

      $date_facturation = $this->generation->arguments->exist('date_facturation') ? Date::getIsoDateFromFrenchDate($this->generation->arguments->get('date_facturation')) : null;

      $handle_factures = fopen(sfConfig::get('sf_web_dir')."/".$sepa_file.".tmp",'a');

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

      //// tmp -> normal
      shell_exec(sprintf("cat %s | iconv -f UTF8 -t ISO-8859-1//TRANSLIT > %s", sfConfig::get('sf_web_dir')."/".$sepa_file.".tmp", sfConfig::get('sf_web_dir')."/".$sepa_file));

      //ajouter l'url de la facture dans la génération.
      if(count($this->generation->documents)) {
         echo("HELLLO");
         echo(sfConfig::get('sf_web_dir')."/".$sepa_file);
          if (filesize(sfConfig::get('sf_web_dir')."/".$sepa_file)) {
              echo("HELLLO");
              $this->generation->add('fichiers')->add(urlencode("/".$sepa_file), 'Export XML des prélèvements');
          }
      }

      //on supprime le .tmp

      shell_exec("rm ".sfConfig::get('sf_web_dir')."/".$sepa_file.".tmp");

      //on sauve notre fichier;
      $this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);
      $this->generation->save();

    }

    public function getDocumentName() {

        return 'ExportXMLSEPA';
    }

}
