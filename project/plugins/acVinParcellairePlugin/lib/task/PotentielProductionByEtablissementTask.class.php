<?php

class PotentielProductionByEtablissementTask extends sfBaseTask
{
    const headers = [
        'Identifiant', 'Parcellaire de référence', 'Produit', 'Désactivé ?', 'Condition', 'Sens', 'Valeur de condition',
        'Cépages concernés', 'Valeur', 'Valeur %', 'Limite', 'Limite %', 'Résultat'
    ];

    public $verbose = false;

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
            new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_OPTIONAL, 'Écris les messages d\'erreurs', false),
            new sfCommandOption('headers', null, sfCommandOption::PARAMETER_OPTIONAL, 'Écris les headers', false),
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

        $this->verbose = $options['verbose'];
        $separateur = $options['separateur'];

        $parcellaire = ParcellaireClient::getInstance()->getLast($arguments['identifiant']);

        if ($parcellaire === null) {
            $this->print(
                sprintf('Pas de parcellaire pour : %s'.PHP_EOL, $arguments['identifiant'])
            );
            return -1;
        }

        if (count($parcellaire->getParcelles()) === 0) {
            $this->print(
                sprintf('Pas de parcelles pour : %s'.PHP_EOL, $arguments['identifiant'])
            );
            return -1;
        }

        $potentiel = PotentielProduction::retrievePotentielProductionFromParcellaire($parcellaire);

        if ($potentiel === null) {
            $this->print(
                sprintf('Pas de potentiel pour : %s'.PHP_EOL, $arguments['identifiant'])
            );
            return -1;
        }

        $out = fopen('php://output', 'w');

        if ($options['headers']) {
            fputcsv($out, self::headers, $separateur);
        }

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
                } elseif (! $rule->isBlockingRule()) {
                    $result = "LIMIT";
                }

                if (! $rule->getResult() && ! $disabled && $rule->isDisabling()) {
                    $disabled = true;
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

    public function print($message)
    {
        if (! $this->verbose) {
            return;
        }

        echo $message;
    }
}
