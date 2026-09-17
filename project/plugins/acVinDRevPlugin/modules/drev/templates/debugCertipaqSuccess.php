<pre>
    <?php print_r($drevOi->getDebugInfo()->getRawValue()); ?>
</pre>
<center>
    <a href="<?php echo url_for('drev_send_certipaq', $drev); echo ($regionParam)? '?region='.$regionParam : ''; ?>" onclick="return confirm('Êtes vous sûr de vouloir envoyer la DRev à Certipaq ?');"  class="btn btn-warning"> Ré-envoyer à certipaq</a>
</center>
