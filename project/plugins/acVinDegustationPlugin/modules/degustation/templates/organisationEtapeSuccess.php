<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>
<?php use_javascript('hamza_style.js'); ?>
<?php use_javascript('degustation.js'); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_ORGANISATION)); ?>

<form action="<?php echo url_for("degustation_organisation_etape", array('sf_subject' => $degustation, 'secteur' => $secteur)) ?>" method="post" class="ajaxForm form-horizontal degustation tournees">
<?php
echo $form->renderHiddenFields();
echo $form->renderGlobalErrors();
?>
    <div class="row" style="margin-top: 20px;">
    <?php if ($degustation->type == DegustationClient::TYPE_MODEL) : ?>
    <div class="col-xs-3">
        <div class="panel panel-default">
        <div class="panel-heading">
            <h2 class="panel-title">
            Liste des tournées
            </h2>
        </div>
        <div class="list-group">
            <?php foreach (array_keys($lots->getRawValue()) as $region): ?>
            <?php if (!count($lots[$region]) && !$afficher_tous_les_secteurs && $region != $secteur && $region != DegustationClient::DEGUSTATION_SANS_SECTEUR): continue; endif; ?>
            <a href="<?php echo url_for('degustation_organisation_etape', array('sf_subject' => $degustation, 'secteur' => $region, 'afficher_tous_les_secteurs' => $afficher_tous_les_secteurs)); ?>" class="list-group-item <?php if($secteur == $region): ?>active<?php endif; ?>">
                <span class="glyphicon <?php if($region == DegustationClient::DEGUSTATION_SANS_SECTEUR): ?>glyphicon-ban-circle<?php else: ?>glyphicon-map-marker<?php endif; ?>"></span> <?php echo $region; ?> <span class="badge"><?php echo count($lots[$region]) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
            <div class="panel-footer text-center">
                <a href="<?php echo url_for('degustation_organisation_etape', array('sf_subject' => $degustation, 'secteur' => $secteur, 'afficher_tous_les_secteurs' => !$afficher_tous_les_secteurs)); ?>" class="btn-link"><?php if(!$afficher_tous_les_secteurs): ?>Afficher tous les secteurs<?php else: ?>Cacher les secteurs non utilisés<?php endif; ?> </a>
            </div>
        </div>
    </div>
    <div class="col-xs-9">
    <?php else: ?>
    <div class="col-xs-12">
    <?php endif; ?>
        <h2 style="margin-top: 0; margin-bottom: 20px;">Tournée <?php echo $secteur ?></h2>
        <table class="table table-bordered table-striped table-condensed">
        <thead>
            <tr>
            <th class="col-xs-3 text-left">Opérateur</th>
            <th class="col-xs-4 text-left">Adresse du logement</th>
            <th class="col-xs-1 text-left">Nombre de lots</th>
            <?php if ($degustation->type == TourneeClient::TYPE_MODEL) : ?>
            <th class="col-xs-2 text-left">Heure</th>
            <?php endif; ?>
            <?php if ($degustation->type == DegustationClient::TYPE_MODEL) : ?>
            <th class="col-xs-2 text-center">Secteur</th>
            <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($form as $logementKey => $subForm):
                $firstlot = $form->getFirstLot($logementKey);
                if ($logementKey == '_revision') continue;
                if (!$firstlot) continue;
            ?>
            <tr class="vertical-center">
                <td class="text-left"><?php echo $firstlot->declarant_nom; ?></td>
                <td class="text-left"><?php echo $firstlot->getLogementNom(); ?><br /><?php echo $firstlot->getLogementAdresse(); ?><br /><?php echo $firstlot->getLogementCodePostal(); ?> <?php echo $firstlot->getLogementCommune(); ?></td>
                <td class="text-center"><?php echo $form->getNbLots($logementKey); ?></td>
                <?php if (isset($subForm['heure'])) : ?>
                <td class="text-center"><?php echo $subForm['heure']->render(); ?></td>
                <?php endif; ?>
                <?php if ($degustation->type == DegustationClient::TYPE_MODEL) : ?>
                <td class="text-center"><?php echo $subForm['logement']->render(); ?></td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </div>
    </div>


    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("degustation_selection_operateurs", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">

        </div>
        <div class="col-xs-4 text-right">
            <a id="btn_suivant" class="btn btn-primary btn-upper pull-right" href="<?php echo url_for('degustation_tournees_etape', $degustation); ?>">
                Étape suivante&nbsp;<span class="glyphicon glyphicon-chevron-right"></span>
            </a>
        </div>
    </div>

</form>
