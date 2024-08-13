<?php use_helper('Lot'); ?>
<?php use_helper('Float'); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>

<div class="page-header no-border">
  <h2>
    <?php if ($degustation->getType() === DegustationClient::TYPE_MODEL): ?>
        Dégustation du
        <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?>
    <?php else: ?>
        Tournée du
        <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR")); ?>
    <?php endif; ?>
    <small><?php echo $degustation->getLieuNom(); ?></small>
  </h2>
</div>

<div class="col-xs-8">
    <h4>Lots <?php if ($degustation->getType() === DegustationClient::TYPE_MODEL) : ?>dégustés<?php else: ?>prélevés<?php endif;?> (<?php echo count($lots->getRawValue()) ?>)</h4>
</div>
<div class="btn-group pull-right">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Documents téléchargeables&nbsp;<span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        <?php if (!$degustation->isTournee()): ?>
            <?php if (!DegustationConfiguration::getInstance()->isTourneeAutonome()): ?>
                <li><a id="btn_pdf_fiche_tournee_prelevement" href="<?php echo url_for('degustation_fiche_lots_a_prelever_pdf', array('sf_subject' => $degustation)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche tournée</a></li>
                <li><a id="btn_pdf_fiche_individuelle_lots_a_prelever" href="<?php echo url_for('degustation_fiche_individuelle_lots_a_prelever_pdf', array('sf_subject' => $degustation)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche de prélèvements</a></li>
                <li><a id="btn_csv_etiquette" href="<?php echo url_for('degustation_etiquette_csv', $degustation) ?>?labo=1"><span class="glyphicon glyphicon-list"></span>&nbsp;Tableur pour labo</a></li>
                <li role="separator" class="divider"></li>
            <?php endif; ?>
            <li><a id="btn_pdf_fiches_proces_verbal" href="<?php echo url_for('degustation_proces_verbal_degustation_pdf', $degustation) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche de procès verbal</a></li>
            <li><a id="btn_degustation_fiche_tables_echantillons_par_dossier_pdf" href="<?php echo url_for('degustation_fiche_tables_echantillons_par_dossier_pdf', $degustation) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiches des lots triées par numéro de dossier</a></li>
            <li><a id="btn_csv_etiquette" href="<?php echo url_for('degustation_etiquette_csv', $degustation) ?>"><span class="glyphicon glyphicon-list"></span>&nbsp;Tableur des lots</a></li>
        <?php else: ?>
            <li><a id="btn_pdf_fiche_tournee_prelevement" href="<?php echo url_for('degustation_fiche_lots_a_prelever_pdf', array('sf_subject' => $degustation)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche tournée</a></li>
            <li><a id="btn_pdf_fiche_individuelle_lots_a_prelever" href="<?php echo url_for('degustation_fiche_individuelle_lots_a_prelever_pdf', array('sf_subject' => $degustation)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche de prélèvements</a></li>
            <li><a id="btn_csv_etiquette" href="<?php echo url_for('degustation_etiquette_csv', $degustation) ?>?labo=1"><span class="glyphicon glyphicon-list"></span>&nbsp;Tableur pour labo</a></li>
        <?php endif; ?>
    </ul>
</div>
<table class="table table-condensed table-bordered table-striped">
    <thead>
        <tr>
            <th title="Date de prélèvement">Date<br/>de prlv</th>
            <th class="col-xs-3">Opérateur</th>
            <th>Prov.</th>
            <th>Nº Lot</th>
            <th class="col-xs-3">Produit</th>
            <th>Volume</th>
            <?php if ($degustation->getType() === DegustationClient::TYPE_MODEL): ?>
                <th>Conformité</th>
                <th>État</th>
            <?php endif ?>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($lots) < 1): ?>
        <tr><td colspan=4 class='text-center'>Aucun lot prélevé</td></tr>
    <?php endif ?>
    <?php foreach ($lots as $k => $lot): ?>
        <tr>
            <td>
                <?php echo DateTimeImmutable::createFromFormat('Y-m-d', $lot->getPreleve())->format('d/m/Y'); ?>
            </td>
            <td><?php echo $lot->declarant_nom ?></td>
            <td class="text-center"><?php echo $lot->getTypeProvenance() ?></td>
            <td class="text-center"><?php echo $lot->numero_dossier ?> / <a href="<?php echo url_for('degustation_lot_historique', ['identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id]) ?>"><?php echo $lot->numero_archive; ?></a></td>
            <td><?php echo showOnlyProduit($lot); ?> <span class="text-muted">Nº <?php echo $lot->numero_logement_operateur ?></span></td>
            <td class="text-right"><?php echoFloat($lot->volume) ?>&nbsp;<small class="text-muted">hl</small></td>
            <?php if ($degustation->getType() === DegustationClient::TYPE_MODEL): ?>
                <td class='text-center'>
                <?php if($lot->hasSpecificitePassage()): ?>
                    <span class="label label-danger" style="margin-right: -14px;">&nbsp;</span>
                <?php endif; ?>
                    <span class="label label-<?php if($lot->isManquement())  { echo 'danger'; }
                                                    elseif ($lot->isConformeObs()) { echo 'warning'; }
                                                    else { echo 'success'; } ?>"
                          style="<?php if($lot->hasSpecificitePassage()): ?>border-radius: 0 0.25em 0.25em 0; border-left: 1px solid #fff;<?php endif; ?>">
                        <span class="glyphicon glyphicon-<?= ($lot->isManquement()) ? 'remove' : 'ok' ?>"></span>
                    </span>
                </td>
                <td><?php  echo showLotStatusCartouche($mvts[$k]->value, null, preg_match("/ème dégustation/", $mvts[$k]->value->libelle));  ?></td>
            <?php endif ?>
            <td class="text-right hidden-print"><a class="btn btn-xs btn-default btn-historique" href="<?php echo url_for('degustation_lot_historique', ['identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id]) ?>">Historique&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<div class="col-xs-12 text-right">
    <?php $etape_devalidation = 'degustation_resultats_etape'; if ($degustation->isTournee()) { $etape_devalidation = 'degustation_prelevements_etape'; }  ?>
    <a title="Les lots de ce documents ont été dégusté, la dévalidation n'est pas possible" class="btn btn-default btn-sm" href="<?php echo url_for($etape_devalidation, $degustation); ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</a>
</div>
