<?php

class GenerationExportParcellaire extends GenerationAbstract
{
    public function generate() {
        /*$this->generation->remove('documents');
        $this->generation->add('documents');*/

        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS);

        $parcellairesfile = "generation/".$this->generation->date_emission."_parcellaires.csv";
        
        $handle_parcellaires = fopen(sfConfig::get('sf_web_dir')."/".$parcellairesfile, 'a');

        if(!count($this->generation->documents)) {
            fwrite($handle_parcellaires, ExportParcellaireCSV::getHeaderCsv());
        }

        $batch_size = 500;
        $batch_i = 1;
		$parcellaires = ParcellaireClient::getInstance()->findAll();

		$etablissements = array();
		
        foreach($parcellaires as $parcellaire) {
        	
        	$etablissement = $parcellaire->identifiant;
        	
        	if (in_array($etablissement, $etablissements)) {
        		continue;
        	}
        	
        	$etablissements[] = $etablissement;
        	
        	$parcellaire = ParcellaireClient::getInstance()->getLast($etablissement);
        	
            if(!$parcellaire) {
                throw new sfException(sprintf("Document %s introuvable", $etablissement));
            }

            $export = new ExportParcellaireCSV($parcellaire, false);
            $this->generation->documents->add(null, $parcellaire->_id);
            fwrite($handle_parcellaires, $export->export());
            $batch_i++;
            if($batch_i > $batch_size) {
              $this->generation->save();
              $batch_i = 1;
            }
        }

        $this->generation->save();
        fclose($handle_parcellaires);
        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);

        if(count($this->generation->documents)) {
            $this->generation->add('fichiers')->add(urlencode("/".$parcellairesfile), 'Export CSV des parcellaires');
        }

        $this->generation->save();
    }

    public function getDocumentName() {
        
        return 'Parcellaire';
    }

} 