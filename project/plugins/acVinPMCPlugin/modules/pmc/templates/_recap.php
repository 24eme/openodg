<?php use_helper('Float') ?>
<?php use_helper('Version') ?>
<?php use_helper('Lot') ?>
<?php use_javascript('hamza_style.js'); ?>

        <?php if($pmc->exist('lots')): ?>
            <h3 id="table_title">Lots de Première mise en marché <?php echo $pmc->isNonConformite() ? PMCNCClient::SUFFIX : '' ?> :</h3>
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
                  <th class="col-xs-1"> Identifiant Lot Opérateur</th>
                <?php else: ?>
                  <th class="col-xs-1"> Identifiant Lot Opérateur</th>
                <?php endif; ?>
                <th class="text-center col-xs-3">Produit - millesime - mention</th>
                <th class="text-center col-xs-1">Volume</th>
                <th class="text-center col-xs-1">Lot prélevable à partir du</th>
                <?php if ($sf_user->isAdminODG()): ?>
                  <th class="text-center col-xs-2">Date de dégustation</th>
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
                        <?php if ($sf_user->isAdminODG()): ?>
                        <td class="text-center">
                            <?php if(isset($form['lots'][$lot->getKey()])): ?>
                                <?php $formItem = $form['lots'][$lot->getKey()]; ?>
                                <?php echo $formItem["date_commission"]->renderError(); ?>
                                <?php if(isset($formItem["degustation"])): ?>
                                <?php echo $formItem['degustation']->renderError(); ?>
                                <?php endif; ?>
                                <div class="formItem-group">
                                    <div class="input-group input-group-sm col-xs-10 date-picker-week" style="z-index: 100px; position: relative;">
                                        <?php if(isset($formItem["degustation"])): ?>
                                        <?php echo $formItem['degustation']->render(); ?>
                                        <?php endif; ?>
                                        <?php echo $formItem["date_commission"]->render(); ?>
                                        <div class="input-group-addon">
                                            <span class="glyphicon-calendar glyphicon"></span>
                                        </div>
                                        <?php if(isset($formItem["degustation"])): ?>
                                        <button type="button" onclick="document.querySelector('#<?php echo $formItem["date_commission"]->renderId() ?>').classList.remove('hidden'); document.querySelector('#<?php echo $formItem["degustation"]->renderId() ?>').classList.add('hidden'); this.classList.add('invisible');
                                        document.querySelector('#<?php echo $formItem["date_commission"]->renderId() ?>').setAttribute('required', true);
                                        document.querySelector('#<?php echo $formItem["degustation"]->renderId() ?>').removeAttribute('required', true); document.querySelector('#<?php echo $formItem["date_commission"]->renderId() ?>').focus()" class="btn btn-link btn-sm" style="position: absolute; right: -60px;">changer</button>
                                        <?php endif; ?>
                                    </div>
                                    <?php if(isset($formItem["degustation"])): ?>
                                    <script>
                                        document.querySelector('#<?php echo $formItem["degustation"]->renderId() ?>').addEventListener('change', function(e) {
                                            document.querySelector('#<?php echo $formItem["date_commission"]->renderId() ?>').value = this.value;
                                        });
                                    </script>
                                    <?php endif; ?>
                                </div>
                            <?php elseif($lot->date_commission): ?>
                                <?php echo $lot->getDateCommissionFr() ?>
                            <?php endif ?>
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
                  <?php if ($sf_user->isAdminODG()): ?>
                    <td></td>
                  <?php endif; ?>
                </tr>
              </tbody>
            </table>
          <?php endif; ?>
