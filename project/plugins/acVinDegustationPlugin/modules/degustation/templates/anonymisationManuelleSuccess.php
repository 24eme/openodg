<?php use_helper('Lot') ?>
<?php use_javascript('degustation.js'); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation, "options" => array("nom" => "Tables des échantillons"))); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_ANONYMISATION_MANUELLE)); ?>

<h2 style="margin-top: 20px; margin-bottom: 10px;">Anonymisation des lots</h2>

<form method="POST" action="<?php echo url_for('degustation_anonymats_etape', ['id' => $degustation->_id]) ?>">
<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>

<div class="input-group" style="margin-bottom: 0; position: relative;">
    <span class="input-group-addon">Filtrer le tableau</span>
    <input id="table_filtre" type="text" class="form-control" placeholder="Rechercher par opérateur, produit ou numéro de logement" autofocus="autofocus" />
    <a href="" id="btn_annuler_filtre" tabindex="-1" class="small hidden" style="z-index: 3; right: 10px; top: 10px; position: absolute;">Annuler la recherche</a>
</div>
<table id="table_anonymisation_manuelle"  style="border-top: 0;" class="table table-bordered table-striped table_lots text-center table_filterable">
  <thead>
    <tr>
      <th class="col-xs-3 text-center">Opérateur</th>
      <th class="col-xs-3 text-center">Produit (millesime)<br/><span class="text-muted">(<?php echo $tri; ?> - <a data-toggle="modal" data-target="#popupTableTriForm" type="button" href="#">changer</a>)</span></th>
      <th class="col-xs-1 text-center">Volume</th>
      <th class="col-xs-1 text-center">Lgmt</th>
      <th class="col-xs-2 text-center">Num. Lot</th>
      <th class="col-xs-2 text-center">Anonymat</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($degustation->getLotsDegustables(true) as $lot): ?>
        <?php $name = $form->getWidgetNameFromLot($lot); ?>
        <tr class="searchable lot <?= ($lot->leurre) ? ' warning' : '' ?>">
            <td class="lot-declarant"><?php echo $lot->declarant_nom ?></td>
            <td class="lot-produit"><?php echo $lot->produit_libelle ?> (<?php echo $lot->millesime ?>)</td>
            <td class="lot-volume text-right"><?php echo $lot->volume ?> <small class="text-muted">hl</small></td>
            <td class="lot-logement"><?php echo $lot->numero_logement_operateur ?></td>
            <td class="lot-numero"><?php echo $lot->numero_dossier . ' / ' . $lot->numero_archive ?></td>
            <td class="lot-anonymat">
                <div class="form-group" style="margin-bottom: 0">
                    <label class="sr-only" for="">Numéro anonymat</label>
                    <div class="input-group">
                        <?php echo $form[$name]->render(['class' => 'form-control']); ?>
                        <div class="input-group-addon">
                            <button type="button" class="close" aria-label="Close" tabindex="-1"><span aria-hidden="true">&times;</span></button>
                        </div>
                    </div>
                </div>
                <?php echo $form[$name]->renderError() ?>
            </td>
        </tr>
    <?php endforeach ?>
    <tr class="hidden"><td colspan="7">Aucun lot trouvé <a id="btn_annuler_filtre_table" href=""><small>(annuler la recherche)</small></a></td></tr>
  </tbody>
</table>
<div class="row">
    <div class="col-xs-4 col-xs-offset-4 text-center">
        <button class="btn btn-sm btn-default ajax" data-toggle="modal" data-target="#popupLeurreForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter un leurre</button>
    </div>
    <div class="col-xs-4 text-right">
        <button type="submit" class="btn btn-primary">
            Confirmer l'anonymat <i class="glyphicon glyphicon-chevron-right"></i>
        </button>
    </div>
</div>
</form>

<?php include_partial('degustation/popupTableTriForm', array('url' => url_for('degustation_tri_table', array('id' => $degustation->_id, 'numero_table' => 0, 'service' => url_for('degustation_anonymats_etape', ['id' => $degustation->_id]))), 'form' => $triTableForm)); ?>
<?php include_partial('degustation/popupAjoutLeurreForm', array('url' => url_for('degustation_ajout_leurre', ['id' => $degustation->_id, 'service' => url_for('degustation_anonymats_etape', ['id' => $degustation->_id])]), 'form' => $ajoutLeurreForm, 'table' => null)); ?>
