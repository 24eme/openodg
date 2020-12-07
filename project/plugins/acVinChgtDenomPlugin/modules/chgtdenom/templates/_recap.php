<?php include_partial('infoLotOrigine', array('lot' => $chgtDenom->getMvtLot())); ?>

<div class="col-sm-12" style="margin-bottom: 20px;">
  <div class="text-center">
    <strong>Devient</strong><br />
    <span class="glyphicon glyphicon-chevron-down"></span>
  </div>
</div>

<?php
  foreach($chgtDenom->lots as $k => $lot):
?>
  <div class="alert col-sm-<?php if (count($chgtDenom->lots) == 1): ?>12<?php else: ?>6<?php endif; ?>" style="background-color: #f8f8f8; border: 1px solid #e7e7e7;">
    <h4>Dossier n°<strong><?php echo $lot->numero_dossier; ?></strong> – Lot n°<strong><?php echo $lot->numero_archive; ?></strong><a href="<?php echo url_for('degustation_etablissement_list',array('id' => $lot->declarant_identifiant))."#".$lot->numero_dossier.$lot->numero_archive; ?>" class="btn btn-default btn-xs pull-right">visu du lot&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a></h4>
    <table class="table table-condensed" style="margin: 0;">
      <tbody>
        <tr>
          <td style="border: none;">Logement : <?php if(!$chgtDenom->isValide()): ?><a href="#" data-toggle="modal" data-target="#modal_lot_<?php echo $k ?>"><strong><?php echo $lot->numero_cuve; ?></strong>&nbsp;<span class="glyphicon glyphicon-edit">&nbsp;</span></a><?php else: ?><strong><?php echo $lot->numero_cuve; ?></strong><?php endif; ?></td>
        </tr>
        <tr>
          <td style="border: none;">Produit : <strong><?php echo $lot->produit_libelle; ?></strong>&nbsp;<small class="text-muted"><?php echo $lot->details; ?></small></td>
        </tr>
        <tr>
          <td style="border: none;">Volume : <strong><?php echoFloat($lot->volume); ?></strong>&nbsp;<small class="text-muted">hl</small></td>
        </tr>
      </tbody>
    </table>
  </div>
  <?php if(!$chgtDenom->isValide()):
        $form = new ChgtDenomLogementForm($lot->getRawValue());
  ?>
    <div class="modal fade" id="modal_lot_<?php echo $k ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form role="form" action="<?php echo url_for("chgtdenom_logement", array("sf_subject" => $chgtDenom, 'key' => "ind$k")) ?>" method="post" class="form-horizontal">
            <?php echo $form->renderHiddenFields(); ?>
            <?php echo $form->renderGlobalErrors(); ?>
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="myModalLabel">Modification du logement <strong><?php echo $lot->numero_cuve ?></strong></h4>
            </div>
            <div class="modal-body">
              <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php echo $form['numero_cuve']->renderLabel("Nouveau logement", array('class' => "col-sm-4 control-label")); ?>
                            <div class="col-sm-8">
                                  <?php echo $form['numero_cuve']->render(); ?>
                            </div>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Fermer</button>
              <button type="submit" class="btn btn-success pull-right">Valider</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>
<?php endforeach; ?>
