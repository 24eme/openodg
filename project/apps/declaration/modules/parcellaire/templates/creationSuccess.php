<?php include_partial('parcellaire/step', array('active' => 'creation', 'identifiant' => $etablissement->identifiant)); ?>

<div class="page-header">
    <h2>Parcellaire - Informations</h2>
</div>

<div class="col-xs-8 col-xs-offset-2">

    <?php include_partial('parcellaire/informationsEtablissement',array('parcellaire' => $parcellaire)); ?>

    <?php include_partial('parcellaire/choixTypeEtablissement'); ?>

</div>

