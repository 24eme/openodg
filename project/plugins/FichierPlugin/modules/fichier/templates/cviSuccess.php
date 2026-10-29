<h1>Détail du CVI n° <?php echo $cvi ; ?></h1>
<?php if (isset($cvi_details[0]) && $cvi_details[0]->libelle): ?>
    <h2><?php echo $cvi_details[0]->libelle; ?></h2>
<?php endif; ?>
<?php if ($etablissement): ?>
    <?php if ($etablissement->isSuspendu()): ?>
        <p class="text-warning">Ce CVI est lié à un établissement SUSPENDU en base sous le nom <a href="<?php echo url_for('etablissement_visualisation', $etablissement); ?>"?><?php echo $etablissement->raison_sociale; ?></a></p>
    <?php else: ?>
        <p class="text-success">Ce CVI est lié à un établissement en base sous le nom <a href="<?php echo url_for('etablissement_visualisation', $etablissement); ?>"><?php echo $etablissement->raison_sociale; ?></a></p>
    <?php endif;?>
<?php else: ?>
<p class="text-warning">Ce CVI n'existe pas en base</p>
<?php endif; ?>
<table class="table">
<?php if (isset($cvi_details[0])): ?>
<?php foreach($cvi_details[0]->getRawValue() as $parentid => $values):
    if (!is_object($values)) { $values = ['' => $values]; }
    foreach($values as $id => $value): if ($value) ?>
    <tr><th><?php echo $parentid ?></th>
        <td><?php echo $id; ?></td>
        <td>
            <pre style="display: inline;"><?php print_r($value); ?></pre>
            <?php if ($parentid == "libelle"): ?><a href="<?php echo url_for('compte_search'); ?>?contacts_all=1&q=<?php echo urlencode($value); ?>">(rechercher)</a><?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
<?php endforeach; ?>
<?php endif; ?>
</table>
<hr/>
<h2>Raw</h2>
<pre><?php print_r(json_encode($cvi_details->getRawValue(), JSON_PRETTY_PRINT)); ?></pre>
