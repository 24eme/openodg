<h1>Détail du CVI n° <?php echo $cvi ; ?></h1>
<?php if (isset($cvi_details[0]) && $cvi_details[0]->libelle): ?>
    <h2><?php echo $cvi_details[0]->libelle; ?></h2>
<?php endif; ?>
<table class="table">
<?php if (isset($cvi_details[0])): ?>
<?php foreach($cvi_details[0]->getRawValue() as $parentid => $values):
    if (!is_object($values)) { $values = ['' => $values]; }
    foreach($values as $id => $value): if ($value) ?>
    <tr><th><?php echo $parentid ?></th><td><?php echo $id; ?></td><td><pre><?php print_r($value); ?></pre></td></tr>
<?php endforeach; ?>
<?php endforeach; ?>
<?php endif; ?>
</table>
<hr/>
<h2>Raw</h2>
<pre><?php print_r(json_encode($cvi_details->getRawValue(), JSON_PRETTY_PRINT)); ?></pre>
