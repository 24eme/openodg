<?php
interface DouaneClient
{
	public function findByArgs($identifiant, $annee);
    public function createDoc($identifiant, $campagne, $papier = false);
}
