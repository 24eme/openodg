<?php

class importScrapeparcellaireTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'provence'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        ));

        $this->namespace        = 'import';
        $this->name             = 'scrape-parcellaire';
        $this->briefDescription = 'Scrape les parcellaires depuis le site des douanes';
        $this->detailedDescription = <<<EOF
La tache [import:scrape-parcellaire|INFO] appelle le script scrapy et va récupérer les
parcellaires liés à l'identifiant configuré.

  [php symfony import:scrape-parcellaire|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        if (empty(glob('/tmp/download_parcellaires.sh*'))) {
            $scrapybin = sfConfig::get('app_scrapy_bin');
            echo exec("$scrapybin/download_parcellaires.sh");
        } else {
            echo "Un autre import est en cours";
        }
    }
}
