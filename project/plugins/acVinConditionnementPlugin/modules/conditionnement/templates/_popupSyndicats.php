<?php $syndicats = $drev->declaration->getSyndicats(); ?>
<?php if(count($syndicats)): ?>
<?php $pos = 20 + 20 * ($sf_user->isUsurpationCompte() || $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)); ?>
<a class="btn btn-default btn-xs" tabindex="-1" style="position: absolute; right:<?php echo $pos ?>px; cursor:pointer;"  data-toggle="modal" data-target="#modalSyndicats" title="contacts de vos syndicats">
  <span class="glyphicon glyphicon-earphone"></span>&nbsp;Contacts ODG
</a>
<div class="modal fade" id="modalSyndicats" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h3 class="modal-title" id="myModalLabel"><?php if(count($syndicats) > 1): ?>Contacts des syndicats<?php else : ?>Contact du syndicat<?php endif; ?></h3>
    </div>
    <div class="modal-body">
      <?php foreach($syndicats as $syndicat): ?>
        <?php $s = DRevConfiguration::getInstance()->getOdgRegionInfos($syndicat); ?>
        <h4><?php echo $s['nom']; ?></h4>
        <div class="row">
          <div class="col-xs-3">Adresse : </div>
          <div class="col-xs-9"><strong><?php echo isset($s['adresse'])? $s['adresse'] : ''; ?></strong></div>
        </div>
        <div class="row">
          <div class="col-xs-3">Téléphone : </div>
          <div class="col-xs-9"><strong><?php echo isset($s['telephone'])? $s['telephone'] : ''; ?></strong></div>
        </div>
        <div class="row">
          <div class="col-xs-3">Email : </div>
          <div class="col-xs-9"><strong><?php echo isset($s['email'])? $s['email'] : ''; ?></strong></div>
        </div>
        <br/>
      <?php endforeach; ?>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
    </div>
  </div>
</div>
</div>
<?php endif; ?>
