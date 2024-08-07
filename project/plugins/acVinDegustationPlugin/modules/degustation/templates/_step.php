<?php
$etapes =  DegustationEtapes::getInstance();
$active = ($active) ? $active : $etapes->getFirst();
?>

<div id="etapes_degustation">
<?php include_partial('global/step', array('object' => $degustation, 'etapes' => $etapes, 'step' => $active)); ?>
</div>

<div style="margin-top: 1rem">
    <?php include_partial('global/flash'); ?>
</div>
