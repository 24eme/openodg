<?php use_helper("Date"); ?>
<?php
$annee = substr($date_ouverture_drev, 0, 4);
$dateFr = format_date($date_ouverture_drev, "dd MMMM", "fr_FR");
?>
<div class="col-xs-4">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3>Déclaration de Revendication&nbsp;<?php echo ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(); ?></h3>
        </div>
        <div class="panel-body">
            Le Téléservice pour la déclaration de revendication <?php echo $annee ?> sera ouvert à partir du <?php echo $dateFr ?>.
        </div>
    </div>
</div>