<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $drap->identifiant, 'campagne' => $drap->campagne)); ?>"><?php echo $drap->getEtablissementObject()->getNom() ?> (<?php echo $drap->getEtablissementObject()->identifiant ?>)</a></li>
  <li class="active"><a href="">Déclaration de renonciation à produire <?php echo $drap->getCampagne(); ?></a></li>
</ol>
