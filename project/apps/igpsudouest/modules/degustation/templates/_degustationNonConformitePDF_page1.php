<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>

<?php
    if ($lot->conformite == Lot::CONFORMITE_NONCONFORME_MINEUR) {
        echo include_partial('degustationNonConformiteMinPDF_page1', array('degustation' => $degustation, 'etablissement' => $etablissement, "lot" => $lot));
    }
    else if ($lot->conformite == Lot::CONFORMITE_NONCONFORME_MAJEUR_CONDITIONNÉ) {
        echo include_partial('degustationNonConformiteConditionnePDF_page1', array('degustation' => $degustation, 'etablissement' => $etablissement, "lot" => $lot));
    } else {
        echo include_partial('degustationNonConformiteNonConditionnePDF_page1', array('degustation' => $degustation, 'etablissement' => $etablissement, "lot" => $lot));
    }
?>
