<ul class="nav nav-tabs" role="tablist">
    <?php foreach($drev->declaration->certification->genre->getAppellations() as $appellation): ?>
    <li class="<?php if(isset($noeud) && $appellation->getHash() == $noeud->getHash()): ?>active<?php endif; ?>"><a role="tab" class="ajax" href="<?php echo url_for("drev_revendication_cepage", $appellation) ?>"><small>AOC Alsace</small><br /><?php echo ucfirst(str_replace("AOC", "", str_replace("d&#039;Alsace", "", str_replace("AOC Alsace ", "", $appellation->getLibelle())))) ?></a></li>
    <?php endforeach; ?>
    <?php if($ajoutForm->hasProduits()): ?>
        <li class="text-center"><a role="tab" data-toggle="modal" data-target="#popupAjoutAppellationForm" href=""><span class="glyphicon glyphicon-plus-sign"></span></a></li>
    <?php endif; ?>
    <li class="text-center pull-right <?php if(isset($step) && $step == 'recapitulatif'): ?>active<?php endif; ?>"><a role="tab" href="<?php echo url_for("drev_revendication_recap", $drev) ?>"><span class="glyphicon glyphicon-th-list"></span><br />Récap.</a></li>
</ul>

<?php include_partial('drev/popupAjoutForm', array('url' => url_for('drev_revendication_appellation_ajout', $drev), 'form' => $ajoutForm, 'html_id' => 'popupAjoutAppellationForm')); ?>