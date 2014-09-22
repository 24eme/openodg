<ul class="nav nav-tabs" role="tablist">
    <li class="<?php if(!isset($noeud)): ?>active<?php endif; ?>"><a role="tab" href="<?php echo url_for("drev_revendication", $drev) ?>"><small>Toutes les</small><br />Appell.</a></li>
    <?php foreach($drev->declaration->certification->genre->getAppellations() as $appellation): ?>
    <li class="<?php if(isset($noeud) && $appellation->getHash() == $noeud->getHash()): ?>active<?php endif; ?>"><a role="tab" class="ajax" href="<?php echo url_for("drev_revendication_cepage", array('sf_subject' => $drev, 'hash' => $appellation->getKey())) ?>"><small>AOC Alsace</small><br /><?php echo ucfirst(str_replace("AOC", "", str_replace("d&#039;Alsace", "", str_replace("AOC Alsace ", "", $appellation->getLibelle())))) ?></a></li>
    <?php endforeach; ?>
</ul>