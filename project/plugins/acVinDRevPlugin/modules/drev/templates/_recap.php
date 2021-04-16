<?php use_helper('Float') ?>
<?php use_helper('Version') ?>
<?php use_helper('Lot') ?>

<?php if ($drev->exist('achat_tolerance') && $drev->get('achat_tolerance')): ?>
  <div class="alert alert-info" role="alert">
    <p>Les volumes récoltés ont fait l'objet d'achats réalisés dans le cadre de la tolérance administrative ou sinistre climatique.</p>
  </div>
<?php endif; ?>

<?php if(count($drev->getProduitsWithoutLots())): ?>
  <h3>Revendication AOP</h3>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <?php if (($drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR) || ($drev->getDocumentDouanierType() == SV11CsvFile::CSV_TYPE_SV11)): ?>
          <th class="col-xs-4"><?php if (count($drev->declaration->getProduitsWithoutLots()) > 1): ?>Produits revendiqués<?php else: ?>Produit revendiqué<?php endif; ?></th>
            <th class="col-xs-2 text-center">Superficie revendiquée&nbsp;<small class="text-muted">(ha)</small></th>
            <th class="col-xs-2 text-center">Volume millesime <?php echo $drev->periode-1 ?> issu du VCI&nbsp;<small class="text-muted">(hl)</small></th>
            <th class="col-xs-2 text-center">Volume issu de la récolte <?php echo $drev->periode ?>&nbsp;<small class="text-muted">(hl)</small></th>
            <th class="col-xs-2 text-center">Volume revendiqué net total&nbsp;<?php if($drev->hasProduitWithMutageAlcoolique()): ?><small>(alcool compris)</small>&nbsp;<?php endif; ?><small class="text-muted">(hl)</small></th>
          <?php else: ?>
            <th class="col-xs-6"><?php if (count($drev->declaration->getProduitsWithoutLots()) > 1): ?>Produits revendiqués<?php else: ?>Produit revendiqué<?php endif; ?></th>
              <th class="col-xs-2 text-center">Superficie revendiquée&nbsp;<small class="text-muted">(ha)</small></th>
              <th class="col-xs-2 text-center">Volume issu de la récolte <?php echo $drev->periode ?>&nbsp;<small class="text-muted">(hl)</small></th>
              <th class="col-xs-2 text-center">Volume revendiqué net total&nbsp;<?php if($drev->hasProduitWithMutageAlcoolique()): ?><small>(alcool compris)</small>&nbsp;<?php endif; ?><small class="text-muted">(hl)</small></th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($drev->declaration->getProduitsWithoutLots() as $produit) : ?>
            <tr>
              <td><?php echo $produit->getLibelleComplet() ?><?php if($produit->isValidateOdg()): ?>&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-ok" ></span><?php endif ?><small class="pull-right <?php if($produit->getRendementEffectif() > $produit->getConfig()->getRendement()): ?>text-danger<?php endif; ?>">&nbsp;<?php echoFloat(round($produit->getRendementEffectif(), 2)); ?> hl/ha</small></td>
              <td class="text-right <?php echo isVersionnerCssClass($produit, 'superficie_revendique') ?>"><?php if($produit->superficie_revendique): ?><?php echoFloat($produit->superficie_revendique) ?> <small class="text-muted">ha</small><?php endif; ?></td>
              <?php if (($drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR) || ($drev->getDocumentDouanierType() == SV11CsvFile::CSV_TYPE_SV11)): ?>
                <td class="text-right <?php echo isVersionnerCssClass($produit, 'volume_revendique_issu_vci') ?>"><?php if($produit->volume_revendique_issu_vci): ?><?php echoFloat($produit->volume_revendique_issu_vci) ?> <small class="text-muted">hl</small><?php endif; ?></td>
              <?php endif; ?>
              <td class="text-right <?php echo isVersionnerCssClass($produit, 'volume_revendique_issu_recolte') ?>"><?php if($produit->volume_revendique_issu_recolte): ?><?php echoFloat($produit->volume_revendique_issu_recolte) ?> <small class="text-muted">hl</small><?php endif; ?></td>
              <td class="text-right <?php echo isVersionnerCssClass($produit, 'volume_revendique_total') ?>"><?php if($produit->volume_revendique_total): ?><?php echoFloat($produit->volume_revendique_total) ?> <small class="text-muted">hl</small><?php endif; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php $bailleurs = $drev->getBailleurs()->getRawValue(); ?>
      <?php if(count($bailleurs)): ?>
        <p style="margin-top: -10px; margin-bottom: 20px;">
          Une partie des volumes ont été récoltés pour le compte <?php if(count($bailleurs) > 1): ?>des<?php else: ?>du<?php endif; ?> bailleur<?php if(count($bailleurs) > 1): ?>s :<?php endif; ?>
            <?php $extra = '' ; foreach($bailleurs as $b): ?>
              <?php  if ($b['etablissement_id'] && $sf_user->hasDrevAdmin()) echo "<a href='".url_for('declaration_etablissement', array('identifiant' => $b['etablissement_id'], 'campagne' => $drev->campagne))."'>" ; ?>
                <?php echo $extra.$b['raison_sociale']; $extra = ', '; ?>
                <?php  if ($b['etablissement_id'] && $sf_user->hasDrevAdmin()) echo " (son espace) </a>"; ?>
              <?php endforeach; ?>.
              Ces volumes seront directement revendiqués par ce<?php if(count($bailleurs) > 1): ?>s<?php endif; ?> bailleur<?php if(count($bailleurs) > 1): ?>s<?php endif; ?>.
            </p>
          <?php endif; ?>
        <?php endif; ?>
        <?php if($drev->exist('lots')): ?>


            <?php
                $lots = $drev->getLotsByCouleur();
                $lotsHorsDR = $drev->getLotsHorsDR();
                $synthese_revendication = $drev->summerizeProduitsLotsByCouleur();
                ?>
              <?php if($dr): ?>
              <h3>Synthèse IGP</h3>
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th class="text-center col-xs-5" style="border-top: hidden; border-left: hidden;"></th>
                    <th class="text-center col-xs-2" colspan="2">DR</th>
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
                      <td class="text-right"><?php  echo (count($lotsByCouleur))? count($lotsByCouleur) : 'aucun lots'; ?></td>
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
            <?php endif; ?>

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
                  foreach ($drev->getLotsByNumeroDossierAndDate() as $lot) :
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

          <?php endif; ?>
          <?php if(count($drev->declaration->getProduitsVci())): ?>
            <h3>Gestion du VCI</h3>
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th class="col-xs-5"><?php if (count($drev->declaration->getProduitsVci()) > 1): ?>Produits revendiqués<?php else: ?>Produit revendiqué<?php endif; ?></th>
                    <th class="text-center col-xs-1">Stock <?php echo $drev->periode - 1 ?><br /><small class="text-muted">(hl)</small></th>
                    <th class="text-center col-xs-1">Rafraichi<br /><small class="text-muted">(hl)</small></th>
                    <th class="text-center col-xs-1">Compl.<br /><small class="text-muted">(hl)</small></th>
                    <th class="text-center col-xs-1">A détruire<br /><small class="text-muted">(hl)</small></th>
                    <th class="text-center col-xs-1">Substitué<br /><small class="text-muted">(hl)</small></th>
                    <th class="text-center col-xs-1">Constitué<br /><?php echo $drev->periode ?>&nbsp;<small class="text-muted">(hl)</small></th>
                    <th class="text-center col-xs-1">Stock <?php echo $drev->periode ?><br /><small class="text-muted">(hl)</small></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($drev->declaration->getProduitsVci() as $produit) : ?>
                    <tr>
                      <td>
                        <?php echo $produit->getLibelleComplet() ?>
                        <small class="pull-right">
                          <span class="<?php if($produit->getRendementVci() > $produit->getConfig()->getRendementVci()): ?>text-danger<?php endif; ?>">&nbsp;<?php echoFloat(round($produit->getRendementVci(), 2)); ?></span>
                          <span data-toggle="tooltip" title="Rendement&nbsp;VCI&nbsp;de&nbsp;l'année&nbsp;| Σ&nbsp;rendement&nbsp;cumulé"
                          class="<?php if($produit->getRendementVciTotal() > $produit->getConfig()->getRendementVciTotal()): ?>text-danger<?php endif; ?>">|&nbsp;Σ&nbsp;<?php echoFloat(round($produit->getRendementVciTotal(), 2)); ?></span>
                          hl/ha </small>
                        </td>
                        <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'stock_precedent') ?>"><?php if($produit->vci->stock_precedent): ?><?php echoFloat($produit->vci->stock_precedent) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                        <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'rafraichi') ?>"><?php if($produit->vci->rafraichi): ?><?php echoFloat($produit->vci->rafraichi) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                        <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'complement') ?>"><?php if($produit->vci->complement): ?><?php echoFloat($produit->vci->complement) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                        <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'destruction') ?>"><?php if($produit->vci->destruction): ?><?php echoFloat($produit->vci->destruction) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                        <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'substitution') ?>"><?php if($produit->vci->substitution): ?><?php echoFloat($produit->vci->substitution) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                        <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'constitue') ?><?php if($produit->getRendementVci() > $produit->getConfig()->getRendementVci()): ?>text-danger<?php endif; ?>"><?php if($produit->vci->constitue): ?><?php echoFloat($produit->vci->constitue) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                        <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'stock_final') ?><?php if($produit->getRendementVciTotal() > $produit->getConfig()->getRendementVciTotal()): ?> text-danger<?php endif; ?>"><?php if($produit->vci->stock_final): ?>
                          <?php if($produit->vci->exist('ajustement')){ echo "(+"; echoFloat($produit->vci->ajustement); echo ") "; } ?>
                          <?php echoFloat($produit->vci->stock_final) ?> <small class="text-muted">hl</small><?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
              <?php if($drev->hasProduitsReserveInterpro()): ?>
                <h3>Réserve interprofessionnelle</h3>
                <table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th class="col-xs-6">Produit</td>
                        <th class="col-xs-3 text-center">Volume mis en réserve</td>
                          <th class="col-xs-3 text-center">Volume revendiqué commercialisable</td>
                          </thead>
                          <tbody>
                            <?php foreach($drev->getProduitsWithReserveInterpro() as $p): ?>
                              <tr>
                                <td><?php echo $p->getLibelle(); ?></td>
                                <td class="text-right"><?php echoFloat($p->getVolumeReserveInterpro()); ?> <small class="text-muted">hl</small></td>
                                <td class="text-right"><?php echoFloat($p->getVolumeRevendiqueCommecialisable()); ?> <small class="text-muted">hl</small></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      <?php endif; ?>

                      <?php use_javascript('hamza_style.js'); ?>
