<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $parcellaireIntentionAffectation->identifiant, 'campagne' => $parcellaireAffectation->campagne - 1)); ?>"><?php echo $parcellaireIntentionAffectation->getEtablissementObject()->getNom() ?> (<?php echo $parcellaireIntentionAffectation->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Intention Parcellaire d'affectation de <?php echo $parcellaireIntentionAffectation->getDate(); ?></a></li>
</ol>
