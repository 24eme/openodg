<?php use_helper('Date'); ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Intention d'affectation parcellaire</h3>
        </div>
          <div class="panel-body">
			  <p>Lorem ipsum</p>
              <div style="margin-top: 50px;">
              	<a class="btn btn-default btn-block" href="<?php echo url_for('parcellaireintentionaffectation_edit', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir la déclaration papier</a>
              </div>
          </div>
    </div>
</div>
            