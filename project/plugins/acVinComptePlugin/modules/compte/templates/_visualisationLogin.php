<div class="row">
<div style="margin-bottom: 5px;" class="col-xs-2  text-muted">Login&nbsp;:</div>
<div style="margin-bottom: 5px;" class="col-xs-3"><?php echo $compte->getLogin(); ?></div>
<?php if (preg_match('/{TEXT}(.*)/', $compte->getSociete()->getMasterCompte()->mot_de_passe, $m)) : ?>
<div style="margin-bottom: 5px;" class="col-xs-3 text-muted">Code de création&nbsp;:</div>
<div style="margin-bottom: 5px;" class="col-xs-3"><?php echo $m[1]; ?></div>
<?php else: ?>
<div style="margin-bottom: 5px;" class="col-xs-6 text-muted">Mot de passe déjà créé</div>
<?php endif; ?>
</div>
