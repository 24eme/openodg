<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('TemplatingPDF'); ?>

<?php
if ($lot->conformite == Lot::CONFORMITE_NONCONFORME_MAJEUR && ($lot->destination_type == "CONDITIONNEMENT" || strpos($lot->id_document_provenance, "CONDITIONNEMENT"))) {
    echo include_partial('degustationNonConformiteConditionnePDF_page2', array('degustation' => $degustation, 'etablissement' => $etablissement, "lot" => $lot));
} else {
    echo include_partial('degustationNonConformiteNonConditionnePDF_page2', array('degustation' => $degustation, 'etablissement' => $etablissement, "lot" => $lot));
}
?>
