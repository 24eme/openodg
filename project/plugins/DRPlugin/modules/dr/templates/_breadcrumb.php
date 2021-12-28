<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $dr->identifiant, 'campagne' => $dr->campagne)); ?>"><?php echo $dr->getEtablissementObject()->getNom() ?> (<?php echo $dr->getEtablissementObject()->identifiant ?> - <?php echo $dr->getEtablissementObject()->cvi ?>)</a></li>
  <li class="active"><a href="">DR de <?php echo $dr->getperiode(); ?></a></li>
</ol>
