<nav class="navbar navbar-default nav-step">
    <ul class="nav navbar-nav">
		<li class="<?php if($current == 'dgcs'): ?>active<?php endif; ?>"><a href="<?php echo url_for("parcellaireAffectation_create", array('sf_subject' => $etablissement, 'campagne' => $campagne, 'papier' => $papier)) ?>" class="">Dénominations complémentaires</a></li>
		<?php foreach ($parcellaireAffectation->getDgc() as $dgcKey => $dgcLibelle): ?>
		<li class="<?php if($dgcKey == $current): ?>active<?php elseif($current == 'dgcs'): ?>disabled<?php endif; ?>"><a href="<?php echo url_for("parcellaireAffectation_edit", array('sf_subject' => $etablissement, 'campagne' => $campagne, 'papier' => $papier, 'lieu' => $dgcKey)) ?>" class=""><?php echo $dgcLibelle ?></a></li>
		<?php endforeach; ?>
		<li class="<?php if($current == 'recap'): ?>active<?php elseif($current == 'dgcs'): ?>disabled<?php endif; ?>"><a href="#" class="">Récapitulatif</a></li>
	</ul>
</nav>