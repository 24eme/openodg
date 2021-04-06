<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $parcellaireIntentionAffectation->identifiant, 'campagne' => $parcellaireIntentionAffectation->campagne)); ?>">
      <?php echo $parcellaireIntentionAffectation->getEtablissementObject()->getNom() ?> (<?php echo $parcellaireIntentionAffectation->getEtablissementObject()->identifiant ?>)
  </a></li>
  <li class="active"><a href="">Identification parcellaire <?php echo $parcellaireIntentionAffectation->getDateFr(); ?></a></li>
</ol>
