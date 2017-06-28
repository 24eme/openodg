<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', $tirage->getEtablissementObject()); ?>"><?php echo $tirage->getEtablissementObject()->getNom() ?> (<?php echo $tirage->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Déclaration de Tirage <?php echo $tirage->getCampagne(); ?></a></li>
</ol>
