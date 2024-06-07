<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $dr->identifiant, 'campagne' => $dr->campagne)); ?>"><?php echo $dr->getEtablissementObject()->getNom() ?> (<?php echo $dr->getEtablissementObject()->identifiant ?> - <?php echo $dr->getEtablissementObject()->cvi ?>)</a></li>
  <li><a href="<?php echo url_for('dr_visualisation', array('id' => $dr->_id)); ?>"><?php if($dr->isBailleur()) echo "Synthèse bailleur "; else echo $dr->type; ?> de <?php echo $dr->getperiode(); ?></a></li>
  <li class="active"><a href="">Vérification</a></li>
</ol>
