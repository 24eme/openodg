<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $ParcellaireAffectation->identifiant, 'campagne' => $ParcellaireAffectation->campagne - 1)); ?>"><?php echo $ParcellaireAffectation->getEtablissementObject()->getNom() ?> (<?php echo $ParcellaireAffectation->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Parcellaire affecté de <?php echo $ParcellaireAffectation->getCampagne(); ?>-<?php echo $ParcellaireAffectation->getCampagne() + 1; ?></a></li>
</ol>
