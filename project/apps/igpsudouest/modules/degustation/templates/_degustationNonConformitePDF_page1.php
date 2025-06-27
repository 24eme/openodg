<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>

<?php
    if ($lot->destination_type == "CONDITIONNEMENT" || strpos($lot->id_document_provenance, "CONDITIONNEMENT")) {
        echo include_partial('degustationNonConformiteConditionnePDF_page1', array('degustation' => $degustation, 'etablissement' => $etablissement, "lot" => $lot));
    } else {
        echo include_partial('degustationNonConformiteNonConditionnePDF_page1', array('degustation' => $degustation, 'etablissement' => $etablissement, "lot" => $lot));
    }
?>
