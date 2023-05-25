<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation, "options" => array("nom" => "Tables des échantillons"))); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_TABLES)); ?>

<div class="row row-condensed">
  <div class="col-xs-3">
      <?php include_partial('degustation/organisationTableManuelleSidebar', compact('degustation', 'numero_table')); ?>
  </div>
  <div class="col-xs-9">
      <h2>Synthèse toutes tables</h2>
      <?php foreach ($degustation->getTables() as $table => $lots): ?>
      <h3>Table <?php echo DegustationClient::getNumeroTableStr($table); ?></h3>
      <table class="table">
        <thead>
            <tr>
                <th class="col-xs-2">Table</th>
                <th class="col-xs-8">Produit</th>
                <th class="col-xs-2">Lots</th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0; ?>
            <?php foreach ($degustation->getSyntheseLotsTable($table) as $hash => $lotsProduit): ?>
                <tr>
                    <td><?php echo DegustationClient::getNumeroTableStr($table) ?></td>
                    <td><?php echo $lotsProduit->libelle ?></td>
                    <td><?php echo count($lotsProduit->lotsTable); $total += count($lotsProduit->lotsTable) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2" class="text-right text-bold"><strong>Total :</strong></td>
                <td><?php echo $total ?> lots</td>
            </tr>
            <tr>
                <td colspan="2" class="text-right text-bold">Dont :</td>
                <td><?php echo count(array_filter($degustation->getLeurres()->getRawValue(), function ($lot) use ($table) {
                    return $lot->numero_table == $table;
                })) ?> leurre(s)</td>
            </tr>
        </tbody>
      </table>
      <?php endforeach ?>

      <div class="row row-margin row-button">
        <div class="col-xs-4">
          <a href="<?php echo url_for("degustation_organisation_table", ['id' => $degustation->_id, 'numero_table' => count($degustation->getTables())]) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Précédent</a>
        </div>
        <div class="col-xs-4 col-xs-offset-4 text-right">
            <a href="<?php echo url_for(DegustationEtapes::getInstance()->getRouteLink(
                DegustationEtapes::getInstance()->getNext(DegustationEtapes::ETAPE_TABLES)
            ), ['id' => $degustation->_id]) ?>" class="btn btn-success btn-upper">
                Terminer
            </a>
        </div>
      </div>
  </div>
</div>
