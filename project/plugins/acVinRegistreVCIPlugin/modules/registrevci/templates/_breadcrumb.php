<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $registre->identifiant, 'campagne' => $registre->campagne)); ?>"><?php echo $registre->getEtablissementObject()->getNom() ?> (<?php echo $registre->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Registre VCI de <?php echo $registre->getCampagne(); ?></a></li>
</ol>
