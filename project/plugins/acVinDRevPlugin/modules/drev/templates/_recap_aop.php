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
