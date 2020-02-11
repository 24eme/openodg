<ul class="nav nav-tabs">
	<?php foreach ($parcellaireAffectation->getDgc() as $dgcKey => $dgcLibelle): ?>
	<li class="<?php if($dgcKey == $current): ?>active<?php elseif($current == 'dgcs'): ?>disabled<?php endif; ?>"><a href="<?php echo url_for("parcellaireaffectation_affectations", array('sf_subject' => $parcellaireAffectation, 'lieu' => $dgcKey)) ?>" class=""><?php echo $dgcLibelle ?></a></li>
	<?php endforeach; ?>
</ul>
