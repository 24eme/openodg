<?php use_helper("Date") ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('constats',array('jour' => date('Y-m-d'))); ?>">Constats VT-SGN</a></li>
  <li><a href="<?php echo url_for('rendezvous_declarant', $rendezvous->getCompte()) ?>"><?php echo $rendezvous->getCompte()->getNomAAfficher() ?> (<?php echo $rendezvous->getCompte()->getIdentifiantAAfficher() ?>)</a></li>
  <li class="active"><a href="">Modification du rendez-vous</a></li>
</ol>

<div class="page-header">
    <h2>Modification du Rendez-vous<br /><span class="text-muted-alt"><?php echo $rendezvous->raison_sociale ?></span></h2>
</div>
<div class="row">
    <div class="col-xs-12">
        <div class="list-group">
                <div class="list-group-item">
                    <?php include_partial('constats/rendezvousForm',array('chai' => $chai, 'form' => $form, 'rendezvous' => $rendezvous, 'creation' => false,'retour' => $retour)); ?>
                </div>
        </div>
    </div>
</div>

<div class="row row-margin">
    <div class="col-xs-12 text-left"><a class="btn btn-danger" href="<?php if($retour == 'planification'): ?><?php echo url_for('constats_planifications', array('date' => $rendezvous->getDate())) ?><?php else: ?><?php echo url_for('rendezvous_declarant', $rendezvous->getCompte()) ?><?php endif; ?>">Annuler</a></div>
</div>