<?php
$etapes =  DegustationEtapes::getInstance();
$active = ($active) ? $active : $etapes->getFirst();
?>

<div id="etapes_degustation">
<?php include_partial('global/step', array('object' => $degustation, 'etapes' => $etapes, 'step' => $active)); ?>
</div>

<?php include_partial('global/flash'); ?>
