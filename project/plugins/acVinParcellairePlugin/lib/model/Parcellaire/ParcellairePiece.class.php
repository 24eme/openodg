<?php
/**
 * Model for ParcellairePiece
 *
 */

class ParcellairePiece extends BaseParcellairePiece {

    public static function getUrlVisualisation($id, $isadmin = false)
    {

        return Parcellaire::getUrlVisualisationPiece($id, $isadmin);
    }
}
