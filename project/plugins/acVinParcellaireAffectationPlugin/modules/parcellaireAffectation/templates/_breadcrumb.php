<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $parcellaireAffectation->identifiant, 'campagne' => $parcellaireAffectation->campagne - 1)); ?>"><?php echo $parcellaireAffectation->getEtablissementObject()->getNom() ?> (<?php echo $parcellaireAffectation->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Parcellaire affecté de <?php echo $parcellaireAffectation->getCampagne(); ?>-<?php echo $parcellaireAffectation->getCampagne() + 1; ?></a></li>
</ol>
