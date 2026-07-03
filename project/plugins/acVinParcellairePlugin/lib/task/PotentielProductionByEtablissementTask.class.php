<?php

class PotentielProductionByEtablissementTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('identifiant', sfCommandArgument::REQUIRED, "Identifiant de l'etablissement"),
        ));
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('separateur', null, sfCommandOption::PARAMETER_OPTIONAL, 'Le séparateur du csv', ';'),
        ));
        $this->namespace = 'potentiel-production';
        $this->name = 'etablissement';
        $this->briefDescription = "Export le potentiel de production par etablissement";
        $this->detailedDescription = "";
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $this->configuration->loadMultiDatabases(null, $databaseManager);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $parcellaire = ParcellaireClient::getInstance()->getLast($arguments['identifiant']);
        $potentiel = PotentielProduction::retrievePotentielProductionFromParcellaire($parcellaire);

        if ($potentiel === null) {
            echo sprintf('Pas de potentiel pour : %s'.PHP_EOL, $arguments['identifiant']);
        }

        $out = fopen('php://output', 'w');
        $separateur = $options['separateur'];

        foreach ($potentiel->getProduits() as $produit) {
            if ($produit->hasPotentiel() === null) {
                continue;
            }

            $disabled = false;
            $parcellaire2ref = $produit->getParcellaire2Ref();
            $base = [$parcellaire->identifiant, $parcellaire2ref->_id];

            foreach ($produit->getRules() as $rule) {
                $result = "NON";
                if ($rule->getResult()) {
                    $result = "OK";
                } elseif ($rule->isBlockingRule()) {
                    $result = "LIMIT";
                }

                fputcsv($out, [
                    ...$base,
                    $produit->getLibelle(),
                    $disabled ? "DISABLED" : null,
                    ...explode(" ", $rule->getLibelle()),
                    implode(", ", $rule->getCepages()),
                    sprintf('%.4f', $rule->getSomme()),
                    $rule->getRegleFonction() === 'ProportionSomme' ? sprintf('%d%%', $rule->getPC() * 100) : null,
                    sprintf('%s %.4f', $rule->getSens(), $rule->getLimit()),
                    $rule->getRegleFonction() === 'ProportionSomme' ? sprintf('%d%%', $rule->getLimitPC() * 100) : null,
                    $result,
                ], $separateur);
            }
        }

        fclose($out);
    }
}
