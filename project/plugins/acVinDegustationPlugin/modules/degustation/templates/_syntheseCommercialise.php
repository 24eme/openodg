<h3>Synthèse Lots</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center col-xs-8">Produit</th>
            <th class="text-center col-xs-2">Volume</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($syntheseLots as $libelle => $volume): ?>
            <tr>
                <td><strong><?php echo $libelle ?></strong></td>
                <td class="text-right"><?php echo echoFloat($volume) ?> <small class="text-muted">hl</small></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
