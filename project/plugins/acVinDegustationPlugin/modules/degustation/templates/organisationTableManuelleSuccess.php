<?php use_helper('Lot') ?>
<?php use_javascript('hamza_style.js'); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation, "options" => array("nom" => "Tables des échantillons"))); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_TABLES)); ?>

<div class="page-header no-border">
  <h2>Attribution des échantillons aux tables</h2>
</div>

<div class="row">
    <div class="col-xs-3">
        <?php include_partial('degustation/organisationTableManuelleSidebar', compact('degustation', 'numero_table', 'tri')); ?>
    </div>
    <div class="col-xs-9 row row-no-gutters">
        <input type="hidden" data-placeholder="Sélectionner un opérateur, un produit ou un numéro de logement" data-hamzastyle-container=".table_lots" data-hamzastyle-mininput="3" class="hamzastyle col-xs-12">
        <table class="table table-bordered table-striped table_lots text-center">
          <thead>
            <tr>
              <th class="col-xs-3 text-center">Opérateur</th>
              <th class="col-xs-4 text-center">Produit (millesime)</th>
              <th class="col-xs-1 text-center">Lgmt</th>
              <th class="col-xs-1 text-center">Num. ODG</th>
              <th class="col-xs-1 text-center">Table</th>
              <th class="col-xs-2 text-center">Anonymat</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($form->getTableLots() as $lot): ?>
                <tr>
                    <td><?php echo $lot->declarant_nom ?></td>
                    <td><?php echo $lot->produit_libelle ?> (<?php echo $lot->millesime ?>)</td>
                    <td><?php echo $lot->numero_logement_operateur ?></td>
                    <td><?php echo $lot->numero_dossier . '-' . $lot->numero_archive ?></td>
                    <td><?php echo DegustationClient::getNumeroTableStr($lot->numero_table) ?></td>
                    <td><?php echo $lot->numero_anonymat ?></td>
                </tr>
            <?php endforeach ?>
          </tbody>
        </table>
    </div>
</div>
