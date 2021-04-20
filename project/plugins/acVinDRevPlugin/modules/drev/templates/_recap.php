<?php use_helper('Float') ?>
<?php use_helper('Version') ?>
<?php use_helper('Lot') ?>

<?php if ($drev->exist('achat_tolerance') && $drev->get('achat_tolerance')): ?>
  <div class="alert alert-info" role="alert">
    <p>Les volumes récoltés ont fait l'objet d'achats réalisés dans le cadre de la tolérance administrative ou sinistre climatique.</p>
  </div>
<?php endif; ?>

<?php if(count($drev->getProduitsWithoutLots())): ?>
<?php    include_partial('drev/recap_aop', array('drev'=>$drev)); ?>
<?php endif; ?>
<?php if($drev->exist('lots')): ?>
<?php    include_partial('drev/recap_igp', array('drev'=>$drev, 'dr' => $dr)); ?>
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
