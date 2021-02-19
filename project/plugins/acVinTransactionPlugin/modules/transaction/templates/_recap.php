<?php use_helper('Float') ?>
<?php use_helper('Version') ?>



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
                    <tr class="<?php echo isVersionnerCssClass($lot, 'produit_libelle') ?> hamzastyle-item" data-callbackfct="$.calculTotal()" data-words='<?php echo json_encode(array($lot->produit_libelle), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>'  >
                      <?php if($transaction->isValidee()): ?>
                        <td><?php echo $lot->numero_archive; ?></td>
                        <td><?php echo $lot->numero; ?></td>
                      <?php else: ?>
                        <td><?php echo $lot->numero; ?></td>
                      <?php endif; ?>
                        <td>
                          <?php echo $lot->produit_libelle; ?>
                          <small >
                          <?php if(DrevConfiguration::getInstance()->hasSpecificiteLot()): ?>
                            <?php echo ($lot->specificite && $lot->specificite != "aucune")? $lot->specificite : ""; ?>
                          <?php endif ?>
                          <?php echo ($lot->millesime)? " ".$lot->millesime."" : ""; ?></small>
                          <?php if(count($lot->cepages)): ?>
                            <br/>
                            <small class="text-muted">
                              <?php echo $lot->getCepagesToStr(); ?>
                            </small>
                          <?php endif; ?>
                        </td>
                        <td class="text-right"><?php echo $lot->pays; ?></td>
                        <td class="text-right"><span class="lot_volume"><?php echoFloat($lot->volume); ?></span><small class="text-muted">&nbsp;hl</small></td>
                        <td class="text-center"><?php echo ($lot->destination_type)? DRevClient::$lotDestinationsType[$lot->destination_type] : ''; echo ($lot->destination_date) ? '<br/><small class="text-muted">'.$lot->getDestinationDateFr()."</small>" : ''; ?></td>
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
                </tr>
              </tbody>
            </table>
            <br/>

          <?php endif; ?>
<?php use_javascript('hamza_style.js'); ?>
