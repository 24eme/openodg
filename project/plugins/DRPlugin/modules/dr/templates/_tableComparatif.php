<table class="table table-bordered">
    <thead>
        <tr>
            <th>Produits</th>
            <th>Superficies issues de la DAP</th>
            <th>Superficies issues de la DR</th>
        </tr>
    </thead>
    <?php if (isset($tableau_comparaison)): ?>
        <?php foreach ($tableau_comparaison as $produit => $valeur): ?>
            <tbody>
                <tr>
                    <div class="row">
                        <td class="col-xs-4"><?php echo $produit; ?></td>
                        <td class="col-xs-3 text-right"><?php echoFloat($valeur['DAP']) ; ?> <small class="text-muted">ha</small></td>
                        <td class="col-xs-3 text-right"><?php echoFloat($valeur['DR']) ; ?> <small class="text-muted">ha</small></td>
                    </div>
                </tr>
            </tbody>
        <?php endforeach; ?>
    <?php else: ?>
        <tbody>
            <tr><td colspan=3><center><i>Pas de données</i></center></td></tr>
        </tbody>
    <?php endif; ?>
</table>
