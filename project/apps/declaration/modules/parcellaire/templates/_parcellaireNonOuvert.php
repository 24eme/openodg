<?php use_helper("Date"); ?>
<?php
$annee = substr($date_ouverture_parcellaire, 0, 4);
$dateFr = format_date($date_ouverture_parcellaire, "dd MMMM", "fr_FR");
?>
<div class="block_declaration panel panel-info">
    <div class="panel-heading">
        <h3>Affectation parcellaire&nbsp;<?php echo ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext(); ?></h3>
    </div>
    <div class="panel-body">
        <p>Le Téléservice pour la déclaration d'affectation parcellaire <?php echo $annee ?> sera ouvert à partir du <?php echo $dateFr ?>.</p>
    </div>
</div>