<h3>Volume Individuel de Production Commercialisable & Certifié (VIP2C)</h3>
<?php $produitLibelle = $drev->declaration->get(DRevConfiguration::getInstance()->getProduitHashWithVolumeSeuil())->getConfig()->getLibelleComplet();?>
<p> Pour le millésime <?php echo(DRevConfiguration::getInstance()->getMillesime());?>, la filière a mis en place le Volume Individuel de Production Commercialisable Certifiée (VIP2C) sur le
<?php echo $produitLibelle ?>. Si vous dépassez le seuil qui vous a été attribué, vous devrez avoir une preuve de commercialisation pour pouvoir revendiquer vos volumes supérieurs.
Le tableau suivant récapitule le volume total revendiqué et le volume seuil qui est associé :</p>
<?php

$td_extra_class = "";
if ( $drev->getVolumeRevendiqueLots($drev->declaration->get(DRevConfiguration::getInstance()->getProduitHashWithVolumeSeuil())->getConfig()->getHash()) - $drev->getVolumeRevendiqueSeuil(DRevConfiguration::getInstance()->getProduitHashWithVolumeSeuil()) < 0) {
    $td_extra_class = " danger text-danger";
}
?>
<table class="table table-bordered table-striped"  style="width:50%;margin-top: 15px;">
  <thead>
    <tr>
      <th scope="col">Produit</th>
      <th scope="col">Volume total revendiqué</th>
      <th scope="col">Volume seuil VIP2C</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th class="<?php echo $td_extra_class; ?>"><?php echo $produitLibelle ?></th>
      <td class="text-right<?php echo $td_extra_class; ?>"><?php echoFloat($drev->getVolumeRevendiqueLots($drev->declaration->get(DRevConfiguration::getInstance()->getProduitHashWithVolumeSeuil())->getConfig()->getHash()), true);?> hl</td>
      <td class="text-right<?php echo $td_extra_class; ?>"><?php echoFloat($drev->getVolumeRevendiqueSeuil(DRevConfiguration::getInstance()->getProduitHashWithVolumeSeuil()), true);?> hl</td>
    </tr>
</tbody>
</table>