<?php use_helper('Lot') ?>
<?php use_javascript('hamza_style.js'); ?>
<?php use_javascript('degustation.js'); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation, "options" => array("nom" => "Tables des échantillons"))); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_TABLES)); ?>

<div class="page-header no-border">
  <h2>Attribution des échantillons aux tables</h2>
</div>

<div class="row">
    <div class="col-xs-3">
        <?php include_partial('degustation/organisationTableManuelleSidebar', compact('degustation', 'numero_table')); ?>
    </div>
    <div class="col-xs-9 row row-no-gutters">
        <input type="hidden" data-placeholder="Sélectionner un opérateur, un produit ou un numéro de logement" data-hamzastyle-container=".table_lots" data-hamzastyle-mininput="3" class="hamzastyle col-xs-12">
        <table id="table_anonymisation_manuelle" class="table table-bordered table-striped table_lots text-center">
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
                <tr class="lot hamzastyle-item<?= ($lot->leurre) ? ' warning' : '' ?>" data-words='<?php echo json_encode([$lot->produit_libelle, $lot->numero_dossier, $lot->numero_logement_operateur, $lot->declarant_nom], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>'>
                    <td class="lot-declarant"><?php echo $lot->declarant_nom ?></td>
                    <td class="lot-produit"><?php echo $lot->produit_libelle ?> (<?php echo $lot->millesime ?>)</td>
                    <td class="lot-logement"><?php echo $lot->numero_logement_operateur ?></td>
                    <td class="lot-numero"><?php echo $lot->numero_dossier . ' / ' . $lot->numero_archive ?></td>
                    <td class="lot-table"><?php echo DegustationClient::getNumeroTableStr($lot->numero_table) ?></td>
                    <td class="lot-anonymat">
                        <div class="form-group"<?php if (! $lot->numero_anonymat): ?> style="display:none"<?php endif ?>>
                            <label class="sr-only" for="">Numéro anonymat</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="" value="<?php echo $lot->numero_anonymat ?>">
                                <div class="input-group-addon">
                                    <button type="button" class="close" aria-label="Close" tabindex="-1"><span aria-hidden="true">&times;</span></button>
                                </div>
                            </div>
                        </div>
                        <?php if (! $lot->numero_anonymat) : ?>
                            <button class="add-to-table" data-table="<?php echo $numero_table ?>">Ajouter à la table</button>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
          </tbody>
        </table>
    </div>
</div>
