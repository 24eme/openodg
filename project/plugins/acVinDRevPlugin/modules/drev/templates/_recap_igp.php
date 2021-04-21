            <?php
                $lots = $drev->getLotsByCouleur();
                $lotsHorsDR = $drev->getLotsHorsDR();
                $synthese_revendication = $drev->summerizeProduitsLotsByCouleur();
                ?>
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
                  <?php foreach ($lots as $couleur => $lotsByCouleur) : ?>
                    <tr>
                      <td><strong><a href="#filtre=<?php echo $couleur; ?>" class="hamzastyle_link" ><?php echo $couleur ?></strong></a><small class="pull-right">&nbsp;<?php if(isset($synthese_revendication[$couleur]) && $synthese_revendication[$couleur]['superficie_totale']): ?><?php echoFloat(round($synthese_revendication[$couleur]['volume_total'] / $synthese_revendication[$couleur]['superficie_totale'], 2)); ?>&nbsp;hl/ha</small><?php endif; ?></td>
                      <td class="text-right"><?php if(isset($synthese_revendication[$couleur]) && $synthese_revendication[$couleur]['superficie_totale']): ?><?php echoFloat($synthese_revendication[$couleur]['superficie_totale']); ?><small class="text-muted">&nbsp;ha</small><?php endif; ?></td>
                      <td class="text-right">
                        <?php if(isset($synthese_revendication[$couleur]) && $synthese_revendication[$couleur]['volume_total']): ?>

                          <?php echoFloat($synthese_revendication[$couleur]['volume_total']); ?><small class="text-muted">&nbsp;hl</small>

                        <?php endif; ?>
                      </td>
                      <td class="text-right">
                          <?php if(isset($synthese_revendication[$couleur]) && $synthese_revendication[$couleur]['nb_lots']): ?>
                              <?php  if ($synthese_revendication[$couleur]['nb_lots'] > 0): ?>
                                  <?php printf("%0.2d", $synthese_revendication[$couleur]['nb_lots_degustables'] / $synthese_revendication[$couleur]['nb_lots'] * 100); ?>%</span> &nbsp;&nbsp; <?php echo $synthese_revendication[$couleur]['nb_lots']; ?>
                              <?php else: ?>
                                   aucun lots
                               <?php endif; ?>
                      <?php elseif(isset($lotsHorsDR[$couleur]) && isset($synthese_revendication[$couleur]['nb_lots'])): ?>
                          <div class="col-xs-6 text-muted text-left"><?php printf("%0.2d", $synthese_revendication[$couleur]['nb_lots_degustables'] / $synthese_revendication[$couleur]['nb_lots'] * 100); ?>%</div> <div class="col-xs-6"><?php echo $synthese_revendication[$couleur]['nb_lots']; ?></div>
                      <?php endif; ?>
                      </td>
                      <td class="text-right">
                        <?php if(isset($synthese_revendication[$couleur]) && $synthese_revendication[$couleur]['volume_lots']): ?>

                          <?php echoFloat($synthese_revendication[$couleur]['volume_lots']); ?><small class="text-muted">&nbsp;hl</small>
                        <?php elseif (isset($lotsHorsDR[$couleur])): ?>
                            <?php echoFloat($lotsHorsDR[$couleur]['volume_lots']); ?><small class="text-muted">&nbsp;hl</small>
                        <?php endif; ?>


                      </td>
                      <td class="text-right">
                        <?php if(isset($synthese_revendication[$couleur]) && round($synthese_revendication[$couleur]['volume_restant'],2) >= 0): ?><?php echoFloat($synthese_revendication[$couleur]['volume_restant']); ?><small>&nbsp;hl</small><?php endif; ?>
                        <?php if(isset($synthese_revendication[$couleur]) && round($synthese_revendication[$couleur]['volume_restant'],2) < 0): ?><span class="text-danger">excédent : +<?php echoFloat($synthese_revendication[$couleur]['volume_restant']*-1); ?><small>&nbsp;hl</small></span><?php endif; ?>
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
                    <tr class="<?php echo isVersionnerCssClass($lot, 'produit_libelle') ?> hamzastyle-item" data-callbackfct="$.calculTotal()" data-words='<?php echo json_encode(array($lot->produit_libelle), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>'  >
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
            <br/>

            <?php
                if(($sf_user->hasDrevAdmin() || $drev->validation) && (count($drev->getProduitsLots()) || count($drev->getLots())) && $drev->isValidee() && $drev->isModifiable()): ?>
                <div class="col-xs-12" style="margin-bottom: 20px;">
                  <a onclick="return confirm('Êtes vous sûr de vouloir revendiquer de nouveaux lots IGP ?')" class="btn btn-primary pull-right" href="<?php echo url_for('drev_modificative', $drev) ?>">Revendiquer des nouveaux lots IGP</a>
                </div>
              <?php endif; ?>


          <?php if($drev->isValidee() && !$drev->isAllDossiersHaveSameAddress()): ?>
          <h3 id="table_igp_title">Chais</h3>
          <table class="table table-bordered table-striped table_igp">
            <thead>
              <tr>
                <th class="col-xs-2 text-center">Num. Dossier</th>
                <th class="col-xs-8 text-center">Détails du chais</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($drev->getLotsByNumeroDossier() as $lot) : ?>
                <?php if($lot->adresse_logement === $drev->constructAdresseLogement()): ?>
                <tr>
                  <td class="text-center"><?php echo $lot->numero_dossier; ?></td>
                  <td class="text-left">
                    <?php echo $lot->adresse_logement;
                    ?>
                  </td>
                </tr>
              <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
