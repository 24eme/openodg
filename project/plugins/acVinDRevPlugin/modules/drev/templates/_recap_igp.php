              <h3>Synthèse IGP</h3>
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th class="text-center col-xs-5" style="border-top: hidden; border-left: hidden;"></th>
                    <th class="text-center col-xs-2" colspan="2">
                        <?php echo $drev->getDocumentDouanierType(); ?> <?php echo substr($drev->campagne, 0, 4); ?> &nbsp;
                        <a href="<?php echo url_for('drev_update_recolte', array('sf_subject' => $drev)); ?>"><span class="glyphicon glyphicon-refresh">&nbsp;</span></a>
                    </th>
                    <th class="text-center col-xs-5" colspan="3">DRev</th>
                  </tr>
                </thead>
                <thead>
                  <tr>
                    <th class="text-center col-xs">Produit (millesime) <small class="pull-right text-muted">Rdmt L5</small></th>
                    <th class="text-center col-xs-1">Superficie</th>
                    <th class="text-center col-xs-1">Volume</th>
                    <th class="text-center col-xs-1">Nb lots</th>
                    <th class="text-center col-xs-1">Vol. revendiqué</th>
                    <th class="text-center col-xs-1">Restant à revendiquer</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($drev->summerizeProduitsLotsByCouleur() as $couleur => $synthese) :
                      $isTotal = (strpos($couleur, 'Total') !== false);
                    ?>
                    <tr <?php if ($isTotal) { echo ' style="font-weight: bold;"'; } ?>>
                      <td>
                          <strong><a href="#filtre=<?php echo $couleur; ?>" class="hamzastyle_link" ><?php echo $synthese['libelle']; ?></strong>
                          <?php if (!$isTotal) : ?>
                              <small class="pull-right">&nbsp;<?php if($synthese['superficie_totale']): ?><?php echoFloat(round($synthese['volume_total'] / $synthese['superficie_totale'], 2), true); ?>&nbsp;hl/ha</small><?php endif; ?>
                          <?php endif; ?>
                      </td>
                      <td class="text-right">
                          <?php if($synthese['superficie_totale']): ?><?php printf("%.04f", $synthese['superficie_totale']); ?><small class="text-muted">&nbsp;ha</small><?php endif; ?>
                      </td>
                        <?php if(isset($synthese['volume_sur_place']) && $synthese['volume_sur_place']): ?>
                            <?php if(isset($synthese['is_precis_sur_place']) && !$synthese['is_precis_sur_place']): ?>
                              <td class="text-right text-warning">
                              <abbr title="Volume max : cet opérateur est apporteur et a du volume en cave. On ne peut pas connaitre précisemment son volume hors lies">⚠</abbr>
                            <?php else: ?>
                              <td class="text-right">
                            <?php endif; ?>
                          <?php echoFloat($synthese['volume_sur_place'], true); ?><small class="text-muted">&nbsp;hl</small>
                          </td>
                         <?php else: ?>
                          <td></td>
                        <?php endif; ?>
                      </td>
                      <td class="text-right">
                              <?php  if ($synthese['nb_lots'] > 0): ?>
                                  <?php if ($isTotal): ?>
                                  <span class="text-muted" data-toggle="tooltip" title="<?= $synthese['nb_lots_degustables'] ?> lot(s) dégusté(s) sur <?= $synthese['nb_lots'] ?> lot(s)">
                                      <?php printf("%0.0f", $synthese['nb_lots_degustables'] / $synthese['nb_lots'] * 100.0); ?>%
                                  </span> &nbsp;&nbsp;
                                  <?php endif;?>
                                  <small data-toggle='tooltip' title="<?= $synthese['nb_lots_degustables'] ?> lot(s) dégusté(s) sur <?= $synthese['nb_lots'] ?> lot(s)"><small class="text-right text-muted"><?= $synthese['nb_lots_degustables'] ?> / </small></small><?php echo $synthese['nb_lots']; ?>
                              <?php else: ?>
                                   aucun lots
                               <?php endif; ?>
                      </td>
                      <td class="text-right">
                          <?php echoFloat($synthese['volume_lots'], true); ?><small class="text-muted">&nbsp;hl</small>
                      </td>
                      <td class="text-right">
                        <?php if(isset($synthese['volume_sur_place']) && $synthese['volume_sur_place']): ?>
                            <?php if(isset($synthese) && round($synthese['volume_restant_max'],2) >= 0): ?><?php echoFloat($synthese['volume_restant_max'], true); ?><small>&nbsp;hl</small><?php endif; ?>
                            <?php if(isset($synthese) && round($synthese['volume_restant_max'],2) < 0): ?><span class="text-danger">excédent : +<?php echoFloat($synthese['volume_restant_max']*-1, true); ?><small>&nbsp;hl</small></span><?php endif; ?>
                        <?php endif; ?>
                      </td>
                      </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>

          <h3 id="table_igp_title">Déclaration des lots IGP</h3>
          <div class="row">
              <input type="hidden" data-placeholder="Sélectionner un produit ou un numéro de dossier" data-hamzastyle-container=".table_igp" data-hamzastyle-mininput="3" class="hamzastyle col-xs-12">
          </div>
          <br/>
          <?php if(!$drev->validation_odg && $sf_user->isAdmin()): ?>
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
                        <td class="text-center"><a title="Historique du lot" href="<?php echo url_for('degustation_lot_historique', ['identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id]) ?>"><?php echo $lot->numero_archive; ?></a></td>
                        <?php endif;?>
                        <td class="text-right"><?php echo $lot->numero_logement_operateur; ?></td>
                        <td>
                          <?php echo showProduitCepagesLot($lot) ?>
                          <?php if($lot->isProduitValidateOdg()): ?>&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-ok" ></span><?php endif ?>
                          <?php if($lot->isInElevage()):?>
                            <br>
                            <small class="text-muted"> en élevage </small>
                          <?php endif; ?>
                        </td>
                        <td class="text-right"><span class="lot_volume"><?php echoFloat($lot->volume, true); ?></span><small class="text-muted">&nbsp;hl</small></td>
                        <td class="text-center"><?php echo ($lot->destination_type)? DRevClient::$lotDestinationsType[$lot->destination_type] : ''; echo ($lot->destination_date) ? '<br/><small class="text-muted">'.$lot->getDestinationDateFr()."</small>" : ''; ?></td>
                        <?php if ($sf_user->isAdmin()): ?>
                        <td class="text-center">
                        <?php if($sf_user->isAdmin() && !$drev->validation_odg && ($lot->id_document == $drev->_id) && isset($form['lots']) && isset($form['lots'][$lot->getKey()])): ?>
                            <div style="margin-bottom: 0;" class="<?php if($form['lots'][$lot->getKey()]->hasError()): ?>has-error<?php endif; ?>">
                              <?php echo $form['lots'][$lot->getKey()]['affectable']->renderError() ?>
                                <div class="col-xs-12">
                                  <?php echo $form['lots'][$lot->getKey()]['affectable']->render(array(
                                                'class' => "drev bsswitch",
                                                "data-preleve-adherent" => "$lot->declarant_identifiant",
                                                "data-preleve-lot" => "$lot->unique_id",'data-size' => 'small',
                                                'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>",
                                                'data-off-text' => "<span class='glyphicon'></span>",
                                                'data-on-color' => "success")
                                            ); ?>
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
                  <td class="text-right"><span class="total_lots"><?php echoFloat($totalVolume, true); ?></span><small class="text-muted">&nbsp;hl</small></td>
                  <td></td>
                  <?php if ($sf_user->isAdmin()): ?>
                    <td></td>
                  <?php endif; ?>
                </tr>
              </tbody>
            </table>


          <h3 id="table_igp_title">Prélèvement</h3>
          <?php if(!$drev->isValidee()): ?>
            <table class="table table-bordered table-striped table_igp">
              <tr>
                <td style="vertical-align : middle;" class="text-left">
                  <?php echo $drev->constructAdresseLogement(); ?>
                  <a href="<?php echo url_for("drev_exploitation", $drev) ?>">(éditer)</a>
                </td>
              </tr>
            </table>
          <?php else: ?>
          <table class="table table-bordered table-striped table_igp">
            <thead>
              <tr>
              <?php if(!$drev->isAllDossiersHaveSameAddress()): ?>
                <th class="col-xs-3 text-center">Num. Dossier</th>
              <?php endif; ?>
                <th class="col-xs-8 text-center">Lieu de prélèvement</th>
              </tr>
            </thead>
            <tbody>
              <?php if($drev->isAllDossiersHaveSameAddress()): ?>
                <tr>
                  <td style="vertical-align : middle;" class="text-left">
                    <?php echo $drev->constructAdresseLogement(); ?>
                  </td>
                </tr>
              <?php else:
                foreach ($drev->getLotsByAdresse() as $address => $lots) : ?>
                <tr>
                  <td class="text-center">
                    <?php
                    $dossiers = array();
                    foreach($lots as $lot){
                      $dossiers[] = $lot->numero_dossier;
                    }
                    echo join(' ; ', $dossiers);
                    ?>
                  </td>
                  <td style="vertical-align : middle;" class="text-left">
                    <?php echo $address; ?>
                  </td>

                </tr>
                <?php endforeach;
              endif; ?>
            </tbody>
          </table>
        <?php endif; ?>
<div class="row">
    <div class="col-xs-12" style="margin-bottom: 20px;">
<?php if($drev->isValideeOdg() && $drev->isModifiable()): ?>
          <a onclick="return confirm('Êtes vous sûr de vouloir revendiquer de nouveaux lots IGP ?')" class="btn btn-primary pull-right" href="<?php echo url_for('drev_modificative', $drev) ?>">Revendiquer de nouveaux lots IGP</a>
<?php elseif(!$drev->isValideeOdg()): ?>
        <div class="pull-right">
          <p class="text-danger">Des lots sont en attente d'approbation</p>
          <p>Vous ne pouvez donc pas en ajouter de nouveaux</p>
        </div>
<?php else: ?>
        <div class="pull-right">
          <p class="text-danger">Cette DREV n'est la dernière et donc pas modifiable</p>
        </div>
<?php endif; ?>
    </div>
</div>
        <hr/>
        <?php if($drev->hasVolumeSeuilAndSetIfNecessary()): ?>
        <?php include_partial('drev/vip2c', array('drev' => $drev, 'form' => $form, 'vip2c' => $vip2c)); ?>
        <hr/>
        <?php endif; ?>

        <?php if (DrevConfiguration::getInstance()->hasDegustation()): ?>
        <h3>Contrôle</h3>
        <?php if(isset($form["date_degustation_voulue"])): ?>
            <?php echo $form["date_degustation_voulue"]->renderError(); ?>
            <div class="form-group" style="margin-bottom: 20px;">
                <?php echo $form["date_degustation_voulue"]->renderLabel("Date de controle des vins souhaitée :", array("class" => "col-xs-3 control-label")); ?>
                <div class="input-group date-picker-week col-xs-3">
                <?php echo $form["date_degustation_voulue"]->render(array("class" => "form-control", "placeholder" => "", "required" => "true")); ?>
                <div class="input-group-addon">
                    <span class="glyphicon-calendar glyphicon"></span>
                </div>
                </div>
            </div>
        <?php else: ?>
            <p>Date de controle souhaitée (hors lots en élevage) : <?php echo ($drev->exist('date_degustation_voulue')) ? date_format(date_create($drev->get('date_degustation_voulue')), 'd/m/Y') : null; ?></p>
        <?php endif; ?>
        <?php if(isset($form["date_commission"])): ?>
            <?php echo $form["date_commission"]->renderError(); ?>
            <?php if(isset($form["degustation"])): ?>
            <?php echo $form['degustation']->renderError(); ?>
            <?php endif; ?>
            <div class="form-group" style="margin-bottom: 20px;">
                <?php echo $form["date_commission"]->renderLabel("Date de la commission :", array("class" => "col-xs-3 control-label")); ?>
                <div class="input-group date-picker-week col-xs-3" style="z-index: 100px; position: relative;">
                    <?php if(isset($form["degustation"])): ?>
                    <?php echo $form['degustation']->render(); ?>
                    <?php endif; ?>
                    <?php echo $form["date_commission"]->render(); ?>
                    <div class="input-group-addon">
                        <span class="glyphicon-calendar glyphicon"></span>
                    </div>
                    <?php if(isset($form["degustation"])): ?>
                    <button type="button" onclick="document.querySelector('#validation_date_commission').classList.remove('hidden'); document.querySelector('#validation_degustation').classList.add('hidden'); this.classList.add('invisible');
                    document.querySelector('#validation_date_commission').setAttribute('required', true);
                    document.querySelector('#validation_degustation').removeAttribute('required', true); document.querySelector('#validation_date_commission').focus()" class="btn btn-link btn-sm" style="position: absolute; right: -80px; top: 10px;">(changer)</button>
                <?php endif; ?>
                </div>
                <script>
                    document.querySelector('#validation_degustation').addEventListener('change', function(e) {
                        document.querySelector('#validation_date_commission').value = this.value;
                    });
                </script>
            </div>
        <?php elseif($drev->date_commission): ?>
            <p>Date de la commission : <?php echo ($drev->exist('date_commission')) ? date_format(date_create($drev->get('date_commission')), 'd/m/Y') : null; ?></p>
            <?php endif ?>
        <?php endif; ?>
