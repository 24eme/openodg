<h3>Volume Individuel de Production Commercialisable & Certifié (VIP2C)</h3>
<p> A partir du millésime <?php echo(VIP2C::getConfigMillesimeVolumeSeuil());?>, la filière a mis en place le Volume Individuel de Production Commercialisable Certifiée (VIP2C) pour certains produit IGP du Sud Est. Si vous dépassez le seuil qui vous a été attribué pour l'un de ces produits, vous devrez avoir une preuve de commercialisation pour pouvoir revendiquer vos volumes supérieurs.
Le tableau suivant récapitule le volume total revendiqué et le volume seuil qui est associé :</p>
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
<?php foreach ($vip2c['produits'] as $produit): ?>
<?php
        $td_extra_class = "";
        if ($produit['volume'] > $produit['volume_max']) {
            $td_extra_class = " danger text-danger";
        }
?>
      <th class="<?php echo $td_extra_class; ?>"><?php echo $produit['libelle'] ?> <?php echo $drev->getDefaultMillesime() ?></th>
      <td class="text-right<?php echo $td_extra_class; ?>"><?php echoFloat($produit['volume'], true);?> hl</td>
      <td class="text-right<?php echo $td_extra_class; ?>"><?php echoFloat($produit['volume_max'], true);?> hl</td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
