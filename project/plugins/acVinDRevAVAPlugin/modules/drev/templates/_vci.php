<?php use_helper('Float') ?>
<h3>Utilisation VCI <?php echo $drev->campagne - 1 ?></h3>
<table class="table table-striped">
        <thead>
            <tr>
                <th class="text-center col-md-4">Appellation</th>
                <th class="text-center col-md-2">Destruction</th>
                <th class="text-center col-md-2">Complément de la récolte</th>
                <th class="text-center col-md-2">Substitution</th>
                <th class="text-center col-md-2">Rafraichis</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($drev->getProduitsVci() as $key => $produit): ?>
            <tr>
                <td><?php echo $produit->getLibelleComplet() ?> <small class="text-muted">-&nbsp;<?php echo $produit->stockage_libelle ?></small></td>
                <td class="text-center"><?php echoFloat($produit->destruction); ?><?php if (!is_null($produit->destruction)): ?> <small class="text-muted">hl</small><?php endif; ?></td>
                <td class="text-center"><?php echoFloat($produit->complement); ?><?php if (!is_null($produit->complement)): ?> <small class="text-muted">hl</small><?php endif; ?></td>
                <td class="text-center"><?php echoFloat($produit->substitution); ?><?php if (!is_null($produit->substitution)): ?> <small class="text-muted">hl</small><?php endif; ?></td>
                <td class="text-center"><?php echoFloat($produit->rafraichi); ?><?php if (!is_null($produit->rafraichi)): ?> <small class="text-muted">hl</small><?php endif; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
</table>


