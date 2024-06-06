<?php

class Csv
{
    /** @var string $filename Nom du fichier */
    private $filename = '';

    /** @var string $separateur Le séparateur de champs */
    private $separateur = '';

    /** @var array $lignes Les lignes du CSV */
    private $lignes = [];

    /** @var array $headers Les entêtes de colonnes */
    private $headers = [];

    /**
     * Constructeur.
     * Prends en parametre le nom du fichier, l'existance
     * d'en tête et le séparateur
     *
     * @param string $filename Le chemin du fichier
     * @param string $separateur Le séparateur de champs
     * @param bool $headers En tête de fichier présent
     *
     * @throws Exception Si une erreur survient lors de la lecture du fichier
     */
    public function __construct($filename, $separateur = ';', $headers = true)
    {
        if (file_exists($filename) && is_readable($filename)) {
            $this->filename = $filename;
            $this->separateur = $separateur;

            $file = fopen($filename, 'r');
            if (! $file) {
                throw new Exception("Fichier non valide");
            }

            while (($d = fgetcsv($file, 1000, $this->separateur)) !== false) {
                $this->lignes[] = $d;
            }

            if ($headers) {
                $this->headers = array_shift($this->lignes);
            }

            fclose($file);
        } else {
            throw new Exception("Le fichier $filename n'existe pas ou n'est pas lisible");
        }
    }

    /**
     * Retourne le nom du fichier
     *
     * @return string Le nom du fichier
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Retourne le caractère de séparation
     *
     * @return string Le séparateur de champs
     */
    public function getSeparateur()
    {
        return $this->separateur;
    }

    /**
     * Retourne les entêtes de colonnes
     *
     * @return array Les entêtes
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Retourne les lignes du CSV via un générateur
     *
     * @return Generator Les lignes une à une
     */
    public function getLignes()
    {
        foreach ($this->lignes as $ligne) {
            yield $ligne;
        }
    }
}
