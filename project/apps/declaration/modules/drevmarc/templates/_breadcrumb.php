<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', $drevmarc->getEtablissementObject()); ?>"><?php echo $drevmarc->getEtablissementObject()->getNom() ?> (<?php echo $drevmarc->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">DRev Marc de <?php echo $drevmarc->getCampagne(); ?></a></li>
</ol>
