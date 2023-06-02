<?php use_helper('Float') ?>
<?php use_helper('Version') ?>
<?php use_helper('Lot') ?>
<?php use_javascript('hamza_style.js'); ?>

        <?php if($pmc->exist('lots')): ?>
          <h3 id="table_title">Déclaration des lots</h3>
          <?php
          $lots = $pmc->getLotsByCouleur();
          ?>
          <div class="row">
              <input type="hidden" data-placeholder="Sélectionner un produit" data-hamzastyle-container=".table_lot" data-hamzastyle-mininput="3" class="hamzastyle col-xs-12">
          </div>
          <br/>
          <table class="table table-bordered table-striped table_lot">
            <thead>
              <tr>
                <?php if($pmc->isValidee()): ?>
                  <th class="col-xs-1"> Numéro Lot ODG</th>
                  <th class="col-xs-1"> Numéro Lot Opérateur</th>
                <?php else: ?>
                  <th class="col-xs-1"> Numéro Lot Opérateur</th>
                <?php endif; ?>
                <th class="text-center col-xs-4">Produit (millesime)</th>
                <th class="text-center col-xs-1">Volume</th>
                <th class="text-center col-xs-2">Date de dégustation souhaitée</th>
                <?php if ($sf_user->isAdmin()): ?>
                  <th class="text-center col-xs-1">Date de dégustation</th>
                <?php endif;?>
              </tr>
            </thead>
            <tbody>
              <?php
              $firstRow = true;
              $totalVolume = 0;
              foreach ($lots as $couleur => $lotsByCouleur) :
                $volume = 0;
                if(count($lotsByCouleur)):
                  foreach ($lotsByCouleur as $lot) :
                    $totalVolume+=$lot->volume;
                    ?>
                    <tr class="hamzastyle-item" data-callbackfct="$.calculTotal()" data-words='<?php echo json_encode(array($lot->produit_libelle), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>'  >
                      <?php if($pmc->isValidee()): ?>
                        <td><a title="Historique du lot" href="<?php echo url_for('degustation_lot_historique', ['identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id]) ?>"><?php echo $lot->numero_archive; ?></a></td>
                        <td><?php echo $lot->numero_logement_operateur; ?></td>
                      <?php else: ?>
                        <td><?php echo $lot->numero_logement_operateur; ?></td>
                      <?php endif; ?>
                        <td>
                          <?php echo showProduitCepagesLot($lot) ?>
                        </td>
                        <td class="text-right"><span class="lot_volume"><?php echoFloat($lot->volume); ?></span><small class="text-muted">&nbsp;hl</small></td>
                        <td class="text-center"><?php echo ($lot->date_degustation_voulue)? $lot->getDateDegustationVoulueFr() : ''; ?></td>
                        <?php if ($sf_user->isAdmin()): ?>
                          <td class="text-center">
                            <?php if(isset($form['lots'])): ?>
                            <div style="margin-bottom: 0;" class="<?php if($form['lots'][$lot->getKey()]->hasError()): ?>has-error<?php endif; ?>">
                              <?php echo $form['lots'][$lot->getKey()]['date_commission']->renderError() ?>
                                <div class="col-xs-12">
                                  <?php if ($sf_user->isAdmin() && !$pmc->validation_odg): ?>
                                    <?php echo $form['lots'][$lot->getKey()]['date_commission']->render(array('class' => "pmc")); ?>
                                  <?php else: ?>
                                      <?php echo $lot->getDateCommissionFr() ?>
                                  <?php endif; ?>
                                </div>
                            </div>
                          <?php else: ?>
                            <div style="margin-bottom: 0;" class="">
                              <div class="col-xs-12">
                                <?php echo $lot->getDateCommissionFr() ?>
                              </div>
                            </div>
                          <?php endif; ?>
                        	</td>
                        <?php endif; ?>
                      </tr>
                      <?php
                      $firstRow=false;
                    endforeach;
                  endif; ?>
                <?php endforeach; ?>
                <tr>
                <?php if($pmc->isValidee()): ?>
                  <td></td>
                <?php endif; ?>
                  <td></td>
                  <td class="text-right">Total : </td>
                  <td class="text-right"><span class="total_lots"><?php echoFloat($totalVolume); ?></span><small class="text-muted">&nbsp;hl</small></td>
                  <td></td>
                  <?php if ($sf_user->isAdmin()): ?>
                    <td></td>
                  <?php endif; ?>
                </tr>
              </tbody>
            </table>
          <?php endif; ?>

