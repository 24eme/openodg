<?php use_helper('Float') ?>
<?php use_helper('Version') ?>
<?php use_helper('Lot') ?>
<?php use_javascript("transaction.js", "last") ?>


        <?php if($transaction->exist('lots')): ?>
          <h3 id="table_igp_title">Déclaration des lots IGP</h3>
          <?php
          $lots = $transaction->getLotsByCouleur();
          ?>
          <div class="row">
              <input type="hidden" data-placeholder="Sélectionner un produit" data-hamzastyle-container=".table_igp" data-hamzastyle-mininput="3" class="select2autocomplete hamzastyle col-xs-12">
          </div>
          <br/>
          <table class="table table-bordered table-striped table_igp">
            <thead>
              <tr>
                <th class="col-xs-1"> Numéro Lot ODG</th>
                <th class="col-xs-1"> Numéro Lot Opérateur</th>
                <th class="text-center col-xs-3">Produit (millesime)</th>
                <th class="text-center col-xs-2">Pays</th>
                <th class="text-center col-xs-1">Volume</th>
                <th class="text-center col-xs-3">Destination (date)</th>
                <th class="text-center col-xs-3">Dégustable</th>
              </tr>
            </thead>

            <?php foreach ($form['lots'] as $key => $formLot): ?>

            <?php endforeach; ?>

            <tbody>
              <?php
              $firstRow = true;
              $totalVolume = 0;
              foreach ($lots as $couleur => $lotsByCouleur) :
                $volume = 0;
                $key = 0;
                if(count($lotsByCouleur)):
                  foreach ($lotsByCouleur as $lot) :
                    $totalVolume+=$lot->volume;
                    ?>
                    <tr class="<?php echo isVersionnerCssClass($lot, 'produit_libelle') ?> hamzastyle-item" data-callbackfct="$.calculTotal()" data-words='<?php echo json_encode(array($lot->produit_libelle), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>'  >
                        <td><?php echo $lot->numero_archive; ?></td>
                        <td><?php echo $lot->numero; ?></td>
                        <td>
                        <?php echo showProduitLot($lot) ?>
                        </td>
                        <td class="text-right"><?php echo $lot->pays; ?></td>
                        <td class="text-right"><span class="lot_volume"><?php echoFloat($lot->volume); ?></span><small class="text-muted">&nbsp;hl</small></td>
                        <td class="text-center"><?php echo ($lot->destination_type)? DRevClient::$lotDestinationsType[$lot->destination_type] : ''; echo ($lot->destination_date) ? '<br/><small class="text-muted">'.$lot->getDestinationDateFr()."</small>" : ''; ?></td>
                        <td class="text-center">
                          <div style="margin-bottom: 0;" class="<?php if($formLot->hasError()): ?>has-error<?php endif; ?>">
                          	<?php echo $formLot['degustable']->renderError() ?>
                              <div class="col-xs-12">
                        	<?php echo $form['lots'][$key]['degustable']->render(array('class' => "transaction bsswitch", "data-preleve-adherent" => "$lot->numero_dossier", "data-preleve-lot" => "$lot->numero_cuve",'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                              </div>
                          </div>
                      	</td>
                      </tr>
                      <?php
                      $key++;
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
                </tr>
              </tbody>
            </table>

            <div class="row row-margin row-button">
                  <div class="col-xs-12 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider</button></div>
            </div>

          <?php endif; ?>
<?php use_javascript('hamza_style.js'); ?>
