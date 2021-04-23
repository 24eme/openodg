              <h3>Synthèse IGP</h3>
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th class="text-center col-xs-5" style="border-top: hidden; border-left: hidden;"></th>
                    <th class="text-center col-xs-2" colspan="2"><?php echo $drev->getDocumentDouanierType(); ?></th>
                    <th class="text-center col-xs-5" colspan="3">DRev</th>
                  </tr>
                </thead>
                <thead>
                  <tr>
                    <th class="text-center col-xs-5">Produit (millesime)</th>
                    <th class="text-center col-xs-1">Superficie</th>
                    <th class="text-center col-xs-1">Volume</th>
                    <th class="text-center col-xs-1">Nb lots</th>
                    <th class="text-center col-xs-1">Vol. revendiqué</th>
                    <th class="text-center col-xs-2">Restant à revendiquer</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($drev->summerizeProduitsLotsByCouleur() as $couleur => $synthese) :
                      $isTotal = strpos($couleur, 'Total') !== false;
                    ?>
                    <tr <?php if ($isTotal) { echo ' style="font-weight: bold;"'; } ?>>
                      <td>
                          <strong><a href="#filtre=<?php echo $couleur; ?>" class="hamzastyle_link" ><?php echo $couleur ?></strong></a>
                          <?php if (!$isTotal) : ?>
                              <small class="pull-right">&nbsp;<?php if(isset($synthese) && $synthese['superficie_totale']): ?><?php echoFloat(round($synthese['volume_total'] / $synthese['superficie_totale'], 2)); ?>&nbsp;hl/ha</small><?php endif; ?>
                          <?php endif; ?>
                      </td>
                      <td class="text-right">
                          <?php if($synthese['superficie_totale']): ?><?php printf("%.04f", $synthese['superficie_totale']); ?><small class="text-muted">&nbsp;ha</small><?php endif; ?>
                      </td>
                      <td class="text-right">
                        <?php if(isset($synthese) && $synthese['volume_total']): ?>
                          <?php echoFloat($synthese['volume_total']); ?><small class="text-muted">&nbsp;hl</small>

                        <?php endif; ?>
                      </td>
                      <td class="text-right">
                              <?php  if ($synthese['nb_lots'] > 0): ?>
                                  <?php if ($isTotal): ?>
                                  <span class="text-muted"><?php printf("%0.2d", $synthese['nb_lots_degustables'] / $synthese['nb_lots'] * 100); ?>%</span> &nbsp;&nbsp;
                                  <?php endif;?>
                                  <?php echo $synthese['nb_lots']; ?>
                              <?php else: ?>
                                   aucun lots
                               <?php endif; ?>
                      </td>
                      <td class="text-right">
                          <?php echoFloat($synthese['volume_lots']); ?><small class="text-muted">&nbsp;hl</small>
                      </td>
                      <td class="text-right">
                        <?php if(isset($synthese) && round($synthese['volume_restant'],2) >= 0): ?><?php echoFloat($synthese['volume_restant']); ?><small>&nbsp;hl</small><?php endif; ?>
                        <?php if(isset($synthese) && round($synthese['volume_restant'],2) < 0): ?><span class="text-danger">excédent : +<?php echoFloat($synthese['volume_restant']*-1); ?><small>&nbsp;hl</small></span><?php endif; ?>
                      </td>
                      </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>

          <h3 id="table_igp_title">Déclaration des lots IGP</h3>
          <div class="row">
              <input type="hidden" data-placeholder="Sélectionner un produit" data-hamzastyle-container=".table_igp" data-hamzastyle-mininput="3" class="select2autocomplete hamzastyle col-xs-12">
          </div>
          <br/>
          <?php if(!$drev->validation_odg): ?>
          <div class="row text-right">
            <div class="col-xs-3 col-xs-offset-9">
              <span>Tout dégustable : <input checked type="checkbox" class="bsswitch" id="btn-degustable-all" data-size = 'small' data-on-text = "<span class='glyphicon glyphicon-ok-sign'></span>" data-off-text = "<span class='glyphicon'></span>" data-on-color = "success"></input>
            </span>

            </div>
          </div>
          <br/>
          <?php endif; ?>
          <table class="table table-bordered table-striped table_igp">
            <thead>
              <tr>
                <th class="col-xs-1">Date Rev.</th>
                <?php if($drev->isValidee()): ?>
                <th class="col-xs-1 text-center">Num. Dossier</th>
                <th class="col-xs-1 text-center">Num. ODG</th>
                <?php endif; ?>
                <th class="col-xs-1 text-right">Lgmt</th>
                <th class="text-center col-xs-5">Produit (millesime)</th>
                <th class="text-center col-xs-1">Volume</th>
                <th class="text-center col-xs-3">Destination (date)</th>
                <?php if ($sf_user->isAdmin()): ?>
                  <th class="text-center col-xs-1">Ctrole</th>
                <?php endif;?>
              </tr>
            </thead>
            <tbody>
              <?php
              $firstRow = true;
              $totalVolume = 0;
                $volume = 0;
                  foreach ($drev->getLotsByUniqueAndDate() as $lot) :
                    $totalVolume+=$lot->volume;
                    ?>
                    <tr class="<?php echo isVersionnerCssClass($lot, 'produit_libelle') ?> hamzastyle-item" data-callbackfct="$.calculTotal()" data-words='<?php echo json_encode(array($lot->produit_libelle, $lot->numero_dossier), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>'  >
                      <td>
                        <?php $drevDocOrigine = $lot->getDrevDocOrigine(); ?>
                        <?php if($drevDocOrigine): ?><a class="link pull-right" href="<?php echo url_for('drev_visualisation', $drevDocOrigine); ?>"><?php endif; ?>
                          <?php echo $lot->getDateVersionfr(); ?>
                          <?php if($drevDocOrigine): ?></a><?php endif; ?>
                        </td>
                        <?php if($drev->isValidee()): ?>
                        <td class="text-center"><?php echo $lot->numero_dossier; ?></td>
                        <td class="text-center"><?php echo $lot->numero_archive; ?></td>
                        <?php endif;?>
                        <td class="text-right"><?php echo $lot->numero_logement_operateur; ?></td>
                        <td>
                          <?php echo showProduitCepagesLot($lot) ?>
                          <?php if($lot->isProduitValidateOdg()): ?>&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-ok" ></span><?php endif ?>
                          <?php if($lot->isInElevage()):?>
                            <p><small class="text-muted"> en élevage </small></p>
                          <?php endif; ?>
                        </td>
                        <td class="text-right"><span class="lot_volume"><?php echoFloat($lot->volume); ?></span><small class="text-muted">&nbsp;hl</small></td>
                        <td class="text-center"><?php echo ($lot->destination_type)? DRevClient::$lotDestinationsType[$lot->destination_type] : ''; echo ($lot->destination_date) ? '<br/><small class="text-muted">'.$lot->getDestinationDateFr()."</small>" : ''; ?></td>
                        <?php if ($sf_user->isAdmin()): ?>
                          <td class="text-center">
                            <?php if(isset($form['lots'])): ?>
                            <div style="margin-bottom: 0;" class="<?php if($form['lots'][$lot->getKey()]->hasError()): ?>has-error<?php endif; ?>">
                              <?php echo $form['lots'][$lot->getKey()]['affectable']->renderError() ?>
                                <div class="col-xs-12">
                                  <?php if ($sf_user->isAdmin() && !$drev->validation_odg): ?>
                                  	<?php echo $form['lots'][$lot->getKey()]['affectable']->render(array('class' => "drev bsswitch", "data-preleve-adherent" => "$lot->declarant_identifiant", "data-preleve-lot" => "$lot->unique_id",'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                                  <?php else: ?>
                                      <?php echo pictoDegustable($lot); ?>
                                  <?php endif; ?>
                                </div>
                            </div>
                          <?php else: ?>
                            <div style="margin-bottom: 0;" class="">
                              <div class="col-xs-12">
                                  <?php echo pictoDegustable($lot); ?>
                              </div>
                            </div>
                          <?php endif; ?>
                        	</td>
                        <?php endif; ?>

                      </tr>
                      <?php
                      $firstRow=false;
                    endforeach; ?>
                <tr>
                <?php
                    $colspan = 2;
                    if ($drev->isValidee()) {
                        $colspan += 2;
                    }
                ?>
                  <td colspan="<?php echo $colspan; ?>"></td>
                  <td class="text-right">Total : </td>
                  <td class="text-right"><span class="total_lots"><?php echoFloat($totalVolume); ?></span><small class="text-muted">&nbsp;hl</small></td>
                  <td></td>
                  <?php if ($sf_user->isAdmin()): ?>
                    <td></td>
                  <?php endif; ?>
                </tr>
              </tbody>
            </table>

          <?php if($drev->isValidee() && !$drev->isAllDossiersHaveSameAddress()): ?>
          <h3 id="table_igp_title">Logement du vin</h3>
          <table class="table table-bordered table-striped table_igp">
            <thead>
              <tr>
                <th class="col-xs-2 text-center">Num. Dossier</th>
                <th class="col-xs-8 text-center">Détails du chais</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($drev->getLotsByNumeroDossier() as $lot) : ?>
                <tr>
                  <td class="text-center"><?php echo $lot->numero_dossier; ?></td>
                  <td class="text-left">
                    <?php echo $drev->getAdresseLogement($lot);
                    ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>

        <br/>

        <?php
            if(($sf_user->hasDrevAdmin() || $drev->validation) && (count($drev->getProduitsLots()) || count($drev->getLots())) && $drev->isValidee() && $drev->isModifiable()): ?>
            <div class="col-xs-12" style="margin-bottom: 20px;">
              <a onclick="return confirm('Êtes vous sûr de vouloir revendiquer de nouveaux lots IGP ?')" class="btn btn-primary pull-right" href="<?php echo url_for('drev_modificative', $drev) ?>">Revendiquer des nouveaux lots IGP</a>
            </div>
        <?php endif; ?>
