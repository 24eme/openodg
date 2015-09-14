<?php use_helper("Date") ?>
<div class="page-header">
    <h2>Création de Rendez-vous<br /><span class="text-muted-alt"><?php echo $compte->nom_a_afficher ?></span></h2>
</div>
<div class="row">    
    <div class="col-xs-12">        
        <div class="list-group">
                <div class="list-group-item">
                    <?php include_partial('constats/rendezvousForm', array('chai' => $chai, 'form' => $form, 'rendezvous' => $form->getObject(), 'creation' => true)); ?> 
                </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 text-left"><a class="btn btn-danger" href="<?php echo url_for('rendezvous_declarant', $compte) ?>">Annuler</a></div>
</div>