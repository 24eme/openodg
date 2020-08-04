<?php

interface InterfaceMouvementFacturesDocument
{
    public function getMouvementsFactures();
    public function getMouvementsFacturesCalcule();
    public function getMouvementsFacturesCalculeByIdentifiant($identifiant);
    public function generateMouvementsFactures();
    public function findMouvementFactures($cle, $id = null);
    public function facturerMouvements();
    public function clearMouvementsFactures();
    public function isFactures();
    public function isNonFactures();
}
