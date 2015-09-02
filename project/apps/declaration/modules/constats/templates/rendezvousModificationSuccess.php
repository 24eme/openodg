<?php use_helper("Date") ?>
<div class="page-header">
    <h2>Rendez vous </h2>
</div>
<div class="row">    
    <div class="col-xs-12">        
        <div class="list-group">
                <div class="list-group-item">
                    <?php include_partial('constats/rendezvousModification',array('chai' => $chai, 'form' => $form, 'rendezvous' => $rendezvous)); ?> 
                </div>
        </div>
    </div>
</div>