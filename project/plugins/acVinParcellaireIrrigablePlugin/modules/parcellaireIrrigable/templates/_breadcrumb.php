<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $parcellaireIrrigable->identifiant, 'campagne' => $parcellaireIrrigable->campagne)); ?>"><?php echo $parcellaireIrrigable->getEtablissementObject()->getNom() ?> (<?php echo $parcellaireIrrigable->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Parcellaire Irrigable de <?php echo $parcellaireIrrigable->getCampagne(); ?>-<?php echo $parcellaireIrrigable->getCampagne() + 1; ?></a></li>
</ol>
