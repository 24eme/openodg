<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_TOURNEES)); ?>

<div class="page-header no-border">
  <h2>Tournées</h2>
</div>
<div class="row">
  <div class="col-xs-offset-3 col-xs-9">
    <h3>Tournée secteur x</h3>
  </div>
  <div class="col-xs-3">
    <div class="panel panel-default" style="min-height: 160px">
      <div class="panel-heading">
        <h2 class="panel-title">
          Secteurs des tournées
        </h2>
      </div>
      <div class="list-group">
        <?php for ($i = 0; $i < 4; $i++): ?>
          <a href="#" class="list-group-item">
            Secteur
            <?php echo $i; ?>
          </a>
        <?php endfor; ?>
      </div>
    </div>
  </div>
  <div class="col-xs-9">
    <table class="table table-bordered table-condensed">
      <thead>
        <tr>
          <th class="col-xs-3 text-left">Opérateur</th>
          <th class="col-xs-4 text-left">Adresse du logement</th>
          <th class="col-xs-2 text-left">Commune du logement</th>
          <th class="col-xs-1 text-left">Nombre de lots</th>
          <th class="col-xs-2 text-left">Secteur</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($degustation->getLotsByOperateurs() as $lots):?>
          <?php $operateur_infos = splitLogementAdresse($lots[0]->getAdresseLogement()); ?>
          <tr class="vertical-center">
            <td class="text-left"><?php echo $operateur_infos['nom']; ?></td>
            <td class="text-left"><?php echo $operateur_infos['adresse']; ?></td>
            <td class="text-left"><?php echo $operateur_infos['commune']; ?> (<?php echo $operateur_infos['code_postal']; ?>)</td>
            <td class="text-left"><?php echo count($lots); ?></td>
            <td class="text-left">secteur x</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="row row-button">
  <div class="col-xs-4"><a href="<?php echo url_for("degustation"); ?>" class="btn btn-default btn-upper"><span
        class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
  <div class="col-xs-4 text-center">
  </div>
  <div class="col-xs-4 text-right"><a id="btn_suivant" <?php if (!$infosDegustation["nbLotsPrelevesSansLeurre"]):
    echo 'disabled="disabled"';
  endif; ?> class="btn btn-primary btn-upper"
      href="<?php echo ($infosDegustation["nbLotsPrelevesSansLeurre"]) ? url_for('degustation_tables_etape', $degustation) : "#"; ?>">Valider&nbsp;<span
        class="glyphicon glyphicon-chevron-right"></span></a></div>
</div>