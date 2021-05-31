<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $parcellaireAffectationCoop->identifiant, 'campagne' => $parcellaireAffectationCoop->periode - 1)); ?>"><?php echo $parcellaireAffectationCoop->getEtablissementObject()->getNom() ?> (<?php echo $parcellaireAffectationCoop->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Affectations parcellaires des apporteurs <?php echo $parcellaireAffectationCoop->getPeriode(); ?></a></li>
</ol>
