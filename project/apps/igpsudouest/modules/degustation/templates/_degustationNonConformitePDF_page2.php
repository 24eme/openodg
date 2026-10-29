<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('TemplatingPDF'); ?>

<?php
    if ($lot->conformite == Lot::CONFORMITE_NONCONFORME_MINEUR) {
        echo include_partial('degustationNonConformiteMinPDF_page2', array('degustation' => $degustation, 'etablissement' => $etablissement, "lot" => $lot));
    }
    else if ($lot->conformite == Lot::CONFORMITE_NONCONFORME_MAJEUR_CONDITIONNÉ) {
        echo include_partial('degustationNonConformiteConditionnePDF_page2', array('degustation' =>     $degustation, 'etablissement' => $etablissement, "lot" => $lot));
    } else {
        echo include_partial('degustationNonConformiteNonConditionnePDF_page2', array('degustation' =>  $degustation, 'etablissement' => $etablissement, "lot" => $lot));
    }
?>
