<?php use_helper('Float') ?>
<?php use_helper('Version') ?>
<?php use_helper('Lot') ?>


        <?php if($transaction->exist('lots')): ?>
          <h3 id="table_igp_title">Déclaration des lots IGP</h3>
          <?php
          $lots = $transaction->getLotsByCouleur();
          ?>
          <div class="row">
              <input type="hidden" data-placeholder="Sélectionner un produit" data-hamzastyle-container=".table_igp" data-hamzastyle-mininput="3" class="select2autocomplete hamzastyle col-xs-12">
          </div>
          <br/>
          <?php if(!$transaction->validation_odg): ?>
          <div class="row">
            <div class="col-xs-2 col-xs-offset-10">
              <a class="btn btn-block btn-default" id="btn-preleve-all">
                <i class="glyphicon glyphicon-ok-sign"></i>
                Tout dégustable
              </a>
            </div>
          </div>
          <br/>
          <?php endif; ?>
          <table class="table table-bordered table-striped table_igp">
            <thead>
              <tr>
              <?php if($transaction->isValidee()): ?>
                <th class="col-xs-1"> Numéro Lot ODG</th>
                <th class="col-xs-1"> Numéro Lot Opérateur</th>
              <?php else: ?>
                <th class="col-xs-1"> Numéro Lot Opérateur</th>
              <?php endif; ?>
                <th class="text-center col-xs-3">Produit (millesime)</th>
                <th class="text-center col-xs-2">Pays</th>
                <th class="text-center col-xs-1">Volume</th>
                <th class="text-center col-xs-3">Destination (date)</th>
                <?php if ($sf_user->isAdmin()): ?>
                  <th class="text-center col-xs-3">Dégustable</th>
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
                      <?php if($transaction->isValidee()): ?>
                        <td><?php echo $lot->numero_archive; ?></td>
                        <td><?php echo $lot->numero_logement_operateur; ?></td>
                      <?php else: ?>
                        <td><?php echo $lot->numero_logement_operateur; ?></td>
                      <?php endif; ?>
                        <td>
                        <?php echo showProduitLot($lot) ?>
                        </td>
                        <td class="text-right"><?php echo $lot->pays; ?></td>
                        <td class="text-right"><span class="lot_volume"><?php echoFloat($lot->volume); ?></span><small class="text-muted">&nbsp;hl</small></td>
                        <td class="text-center"><?php echo ($lot->destination_type)? DRevClient::$lotDestinationsType[$lot->destination_type] : ''; echo ($lot->destination_date) ? '<br/><small class="text-muted">'.$lot->getDestinationDateFr()."</small>" : ''; ?></td>
                        <?php if ($sf_user->isAdmin()): ?>
                          <td class="text-center">
                            <?php if(isset($form['lots'])): ?>
                            <div style="margin-bottom: 0;" class="<?php if($form['lots'][$lot->getKey()]->hasError()): ?>has-error<?php endif; ?>">
                              <?php echo $form['lots'][$lot->getKey()]['affectable']->renderError() ?>
                                <div class="col-xs-12">
                                  <?php if ($sf_user->isAdmin() && !$transaction->validation_odg): ?>
                                  	<?php echo $form['lots'][$lot->getKey()]['affectable']->render(array('class' => "transaction bsswitch", "data-preleve-adherent" => "$lot->numero_dossier", "data-preleve-lot" => "$lot->numero_logement_operateur",'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
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
                    endforeach;
                  endif; ?>
                <?php endforeach; ?>
                <tr>
                  <?php if($transaction->isValidee()): ?>
                    <td></td>
                  <?php endif; ?>
                  <td></td>
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
<?php use_javascript('hamza_style.js'); ?>
