<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>
<?php use_javascript('degustation.js') ?>
<?php use_javascript('hamza_style.js'); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation, 'options' => array('route' => 'degustation_preleve', 'nom' => 'Prélevements réalisés'))); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_PRELEVEMENTS)); ?>

<div class="page-header no-border">
  <h2>Échantillons prélevés</h2>
  <h3><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?> <small><?php echo $degustation->getLieuNom(); ?></small></h3>
</div>

<?php include_partial('global/flash'); ?>

<?php include_partial('degustation/synthese', array('degustation' => $degustation, 'infosDegustation' => $infosDegustation)); ?>

<div class="row">
  <div class="form-group col-xs-10">
    <p>Sélectionner les lots qui ont été prélevés</p>
  </div>

  <div class="col-xs-2">
    <button class="btn btn-block btn-default" id="btn-preleve-all">
      <i class="glyphicon glyphicon-ok-sign"></i>
      Tout prélever
    </button>
  </div>
</div>

<div class="input-group" style="margin-bottom: 0; position: relative;">
    <span class="input-group-addon">Filtrer le tableau</span>
    <input id="table_filtre" type="text" class="form-control" placeholder="Rechercher par opérateur, produit ou numéro de logement" autofocus="autofocus" />
    <a href="" id="btn_annuler_filtre" tabindex="-1" class="small hidden" style="z-index: 3; right: 10px; top: 10px; position: absolute; color: grey;"><span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span></a>
</div>
<form action="<?php echo url_for("degustation_preleve", $degustation) ?><?php if($differer): ?>?differer=1<?php endif; ?>" method="post" class="ajaxForm form-horizontal degustation prelevements">
	<?php echo $form->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>

    <table class="table table-bordered table-condensed table-striped table_filterable" id="table_prelevements">
        <thead>
            <tr>
                <th class="col-xs-2">Opérateur</th>
                <th class="col-xs-1">Provenance</th>
                <th class="col-xs-1">Logement</th>
                <th class="col-xs-3">Produit (millésime, spécificité)</th>
                <th class="col-xs-1">Volume</th>
                <th class="col-xs-1">Prélevé</th>
                <th class="col-xs-1"></th>
            </tr>
        </thead>
		<tbody>
		<?php foreach ($form['lots'] as $key => $formLot): ?>
    <?php $lot = $degustation->lots->get($key); ?>
      <tr class="vertical-center cursor-pointer searchable" data-adherent="<?php echo $lot->declarant_identifiant; ?>">
        <td><?php echo $lot->declarant_nom; ?></td>
        <td><?php echo $lot->getTypeProvenance(); ?> <span class="text-muted">n°<?php echo $lot->numero_dossier; ?></span></td>
        <td class="edit"><?= $lot->numero_logement_operateur ?>
          <?php if (! $lot->isLeurre()): ?>
            <span class="pull-right">
              <a title="Modifier le logement" href="<?php echo url_for('degustation_preleve_update_logement', ['id' => $degustation->_id, 'lot' => $key]) ?>"><i class="glyphicon glyphicon-pencil"></i></a>
            </span>
          <?php endif; ?>
        </td>
        <td>
            <?= showProduitCepagesLot($lot) ?>
        </td>
        <td class="text-right edit ">
              <?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small>
              &nbsp;
              <a class= "ajax" title="Modifier le volume" href="<?php echo url_for("degustation_lot_modification", [
                  'identifiant' => $lot->declarant_identifiant,
                  'unique_id'=> $lot->unique_id,
                  'service' => url_for('degustation_preleve', $degustation)
              ]); ?>">
                <i class="glyphicon glyphicon-share-alt"></i>
              </a>
        </td>
      	<td class="text-center">
              <div style="margin-bottom: 0;" class="<?php if($formLot->hasError()): ?>has-error<?php endif; ?>">
              	<?php echo $formLot['preleve']->renderError() ?>
                  <div class="col-xs-12">
            	<?php echo $formLot['preleve']->render(array('class' => "degustation bsswitch", "data-preleve-adherent" => "$lot->declarant_identifiant", "data-preleve-lot" => "$lot->unique_id",'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                  </div>
              </div>
      	</td>
        <td class="edit text-center">
            <a class="text-muted" onclick="return confirm('Êtes-vous sûr de vouloir supprimer le logement <?php echo $lot->numero_logement_operateur.' de '.$lot->volume."hl" ?> ?');" title="Supprimer le logement" href="<?php echo url_for('degustation_supprimer_lot_non_preleve', ['id' => $degustation->_id, 'lot' => $key]) ?>"><i class="glyphicon glyphicon-trash"></i></a>
            <div class="dropdown pull-right" style="display: inline-block">
                <button class="btn btn-default dropdown-toggle btn-xs" type="button" id="listePdf" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <span class="glyphicon glyphicon-file"></span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="listePdf">
                    <li>
                      <?php if(DegustationConfiguration::getInstance()->hasAnonymat4labo()) : ?>
                          <a id="btn_pdf_etiquettes_de_prelevement" href="<?php echo url_for('degustation_etiquette_pdf', ['id' => $degustation->_id, 'identifiant' => $lot->declarant_identifiant, 'anonymat4labo' => true]) ?>" title="Étiquettes de prélèvement (avec anonymat labo)"><span class="glyphicon glyphicon-th"></span> Étiquettes de prélèvement (avec anonymat labo)</a>
                      <?php else : ?>
                          <a id="btn_pdf_etiquettes_de_prelevement" href="<?php echo url_for('degustation_etiquette_pdf', ['id' => $degustation->_id, 'identifiant' => $lot->declarant_identifiant]) ?>" title="Étiquettes de prélèvement"><span class="glyphicon glyphicon-th"></span> Étiquettes de prélèvement</a>
                      <?php endif ?>
                    </li>
                    <li>
                        <a id="btn_pdf_etiquettes_de_prelevement" href="<?php echo url_for('degustation_fiche_individuelle_lots_a_prelever_pdf', ['id' => $degustation->_id, 'lotid' => $lot->unique_id]) ?>" title="Fiche tournée prélevement"><span class="glyphicon glyphicon-file"></span> Fiche tournée prélevement</a>
                    </li>
                </ul>
            </div>
        </td>
      </tr>
    <?php endforeach; ?>
      <tr class="hidden"><td colspan="7">Aucun lot trouvé <a id="btn_annuler_filtre_table" href=""><small>(annuler la recherche)</small></a></td></tr>
    </tbody>
	</table>

	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("degustation_saisie_etape", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider</button></div>
    </div>
</form>
</div>
