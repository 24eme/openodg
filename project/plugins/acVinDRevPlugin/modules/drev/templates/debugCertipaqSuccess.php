<h1>Envoi de la DREV à Certipaq</h1>
<h2>Synthèse de l'envoi</h2>
<ul>
<?php foreach ($drevOi->getDebugInfo()->getRawValue() as $hash => $res): ?>
<li>
    <?php echo $hash; ?> :
    <?php if ($res->success): ?>
        <span class="text-success">OK - <?php echo $res->res->id; ?></span>
    <?php else: ?>
        <span class="text-danger">ERROR - <?php echo $res->erreors; ?></span>
    <?php endif; ?>
</li>
<?php endforeach; ?>
</ul>
<center>
    <a href="<?php echo url_for('drev_send_certipaq', $drev); echo ($regionParam)? '?region='.$regionParam : ''; ?>" onclick="return confirm('Êtes vous sûr de vouloir envoyer la DRev à Certipaq ?');"  class="btn btn-warning"> Ré-envoyer à certipaq</a>
</center>
<h2>Données brutes</h2>
<pre>
    <?php print_r($drevOi->getDebugInfo()->getRawValue()); ?>
</pre>
<center>
    <a href="<?php echo url_for('drev_send_certipaq', $drev); echo ($regionParam)? '?region='.$regionParam : ''; ?>" onclick="return confirm('Êtes vous sûr de vouloir envoyer la DRev à Certipaq ?');"  class="btn btn-warning"> Ré-envoyer à certipaq</a>
</center>
