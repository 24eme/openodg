<?php

class LotsExportSuiviCsvTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'lots';
    $this->name             = 'export-suivi-csv';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    $context = sfContext::createInstance($this->configuration);

    $lots = array();
    foreach(MouvementLotView::getInstance()->getByStatut(Lot::STATUT_DEGUSTE)->rows as $lot) {
        $lots[$lot->value->declarant_identifiant.$lot->value->unique_id] = $lot->value;
    }

    echo "Origine;Id Opérateur;Nom Opérateur;Campagne;Num dossier;Num lot;Num logement Opérateur;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;Produit;Millésime;Volume;Origine Date;1ere Degustation Date;1ere Degustation Statut;1ere Degustation Statut Libelle;1ere Degustation Detail;2eme Degustation Date;2eme Degustation Statut;2eme Degustation Statut Libelle;2eme Degustation Detail;Issue Date;Issue Statut;Issue Statut Libelle;Issue Detail;Organisme;Doc Id;Lot unique Id;Declarant Lot unique Id;Hash produit\n";

    foreach($lots as $lot) {
        $suivi = LotsClient::getInstance()->getSuivi($lot->declarant_identifiant, $lot->unique_id);

        if(!$suivi) {
            continue;
        }

        $produit = explode('/', str_replace('DEFAUT', '', $lot->produit_hash));

        $ligne = "";
        $ligne .= $suivi['ORIGINE']['INITIAL_TYPE'].";";
        $ligne .= $lot->declarant_identifiant.";";
        $ligne .= VarManipulator::protectStrForCsv($lot->declarant_nom).";";
        $ligne .= $lot->campagne.";";
        $ligne .= $lot->numero_dossier.";";
        $ligne .= $lot->numero_archive.";";
        $ligne .= VarManipulator::protectStrForCsv($lot->numero_logement_operateur).";";
        $ligne .= $produit[3].";";
        $ligne .= $produit[5].";";
        $ligne .= $produit[7].";";
        $ligne .= $produit[9].";";
        $ligne .= $produit[11].";";
        $ligne .= $produit[13].";";
        $ligne .= $produit[15].";";
        $ligne .= VarManipulator::protectStrForCsv($lot->produit_libelle).";";
        $ligne .= $lot->millesime.";";
        $ligne .= VarManipulator::floatizeForCsv($lot->volume).";";
        $ligne .= $suivi['ORIGINE']['DATE'].";";
        $ligne .= $suivi['DEGUSTATION'][0]['DATE'].";";
        $ligne .= $suivi['DEGUSTATION'][0]['STATUT'].";";
        $ligne .= VarManipulator::protectStrForCsv($suivi['DEGUSTATION'][0]['STATUT_LIBELLE']).";";
        $ligne .= (isset($suivi['DEGUSTATION'][0]['DETAIL']) ? VarManipulator::protectStrForCsv($suivi['DEGUSTATION'][0]['DETAIL']) : null).";";
        $ligne .= $suivi['DEGUSTATION'][1]['DATE'].";";
        $ligne .= $suivi['DEGUSTATION'][1]['STATUT'].";";
        $ligne .= VarManipulator::protectStrForCsv($suivi['DEGUSTATION'][1]['STATUT_LIBELLE']).";";
        $ligne .= (isset($suivi['DEGUSTATION'][1]['DETAIL']) ? VarManipulator::protectStrForCsv($suivi['DEGUSTATION'][1]['DETAIL']) : null).";";
        $ligne .= $suivi['ISSUE']['DATE'].";";
        $ligne .= $suivi['ISSUE']['STATUT'].";";
        $ligne .= VarManipulator::protectStrForCsv($suivi['ISSUE']['STATUT_LIBELLE']).";";
        $ligne .= VarManipulator::protectStrForCsv($suivi['ISSUE']['DETAIL']).";";
        $ligne .= Organisme::getCurrentOrganisme().";";
        $ligne .= $lot->id_document.";";
        $ligne .= $lot->unique_id.";";
        $ligne .= $lot->declarant_identifiant.'-'.$lot->unique_id.";";
        $ligne .= $lot->produit_hash.";";

        echo $ligne."\n";
    }
  }
}
