<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $parcellaireAffectation->identifiant, 'campagne' => $parcellaireAffectation->campagne)); ?>"><?php echo $parcellaireAffectation->getEtablissementObject()->getNom() ?> (<?php echo $parcellaireAffectation->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Affectation parcellaire <?php echo $parcellaireAffectation->getCampagne(); ?></a></li>
</ol>
