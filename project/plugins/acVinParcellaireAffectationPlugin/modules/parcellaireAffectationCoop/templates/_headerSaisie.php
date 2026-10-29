<?php if($hasForm): ?>
<button type="submit" name="retour" value="1" class="btn btn-default pull-right mr-3"><span class="glyphicon glyphicon-chevron-left"></span> Retour à la liste</button>
<?php else: ?>
<a href="<?php echo url_for("parcellaireaffectationcoop_liste", $parcellaireAffectationCoop) ?>" class="btn btn-default pull-right"><span class="glyphicon glyphicon-chevron-left"></span> Retour à la liste</a>
<?php endif; ?>
<h3 class="mt-2 mb-2"><span class="glyphicon glyphicon-home"></span> <?php echo $declaration->declarant->getNom() ?> (<?php echo $declaration->declarant->cvi; ?>)</h3>
