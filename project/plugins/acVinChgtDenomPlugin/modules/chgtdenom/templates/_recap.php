<style>
  #declassement_filigrane{
    position:absolute;
    font-size: 4em;
    top: 22%;
    left: 37%;
    rotate: -33deg;
    opacity: 0.3;
    text-transform: uppercase;
  }
  .block-chgtDenom{
    background-color: #f8f8f8;
    border: 1px solid #e7e7e7;
  }
</style>
<?php include_partial('infoLotOrigine', array('chgtDenom' => $chgtDenom, 'opacity' => true)); ?>

<div class="col-sm-12 mb-5">
  <div class="text-center">
    <strong>Devient</strong><br />
    <span class="glyphicon glyphicon-chevron-down"></span>
  </div>
</div>

<?php
  foreach($chgtDenom->lots as $k => $lot):
?>
  <div class="alert block-chgtDenom col-sm-<?php if (count($chgtDenom->lots) == 1): ?>12<?php else: ?>6<?php endif; ?>">
  <?php if($chgtDenom->changement_type == ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT): ?>
    <div id="declassement_filigrane" class="text-danger">Déclassé</div>
  <?php endif; ?>
    <h4>Dossier n°<strong><?php echo $lot->numero_dossier; ?></strong> – Lot n°<strong><?php echo $lot->numero_archive; ?></strong><?php if($chgtDenom->isValidee()): ?><a href="<?php echo url_for('degustation_etablissement_list',array('id' => $lot->declarant_identifiant))."#".$lot->numero_dossier.$lot->numero_archive; ?>" class="btn btn-default btn-xs pull-right">visu du lot&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a><?php endif; ?></h4>
    <table class="table table-condensed" style="margin: 0;">
      <tbody>
        <tr>
          <td>
            <div>
              <div style="border: none;" class="m-3">
                Logement :
                <?php if(!$chgtDenom->isValide()): ?>
                  <a href="#" data-toggle="modal" data-target="#modal_lot_<?php echo $k ?>">
                    <strong><?php echo $lot->numero_logement_operateur; ?></strong>&nbsp;<span class="glyphicon glyphicon-edit">&nbsp;</span>
                  </a>
                <?php else: ?>
                  <strong><?php echo $lot->numero_logement_operateur; ?></strong>
                <?php endif; ?>
              </div>

              <?php if($chgtDenom->changement_type == ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT): ?>
              <div style="border: none;" class="m-3">
                Produit : <strong><?php echo showProduitLot($lot) ?></strong>
              </div>
              <?php endif; ?>

              <div style="border: none;" class="m-3">Volume : <strong><?php echoFloat($lot->volume); ?></strong>&nbsp;<small class="text-muted">hl</small></div>
            </div>
          </td>
          <td>
            <?php if ($sf_user->isAdmin() && $chgtDenom->changement_type == ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT): ?>
              <div class="text-center">
                <?php if(isset($form['lots'])): ?>
                <div style="margin-bottom: 0;" class="<?php if($form['lots'][$lot->getKey()]->hasError()): ?>has-error<?php endif; ?>">
                  <?php echo $form['lots'][$lot->getKey()]['affectable']->renderError() ?>
                    <div class="col-xs-12">
                      <?php if ($sf_user->isAdmin() && !$chgtDenom->validation_odg): ?>
                        <span>Dégustable&nbsp;&nbsp; :</span>
                        <?php echo $form['lots'][$lot->getKey()]['affectable']->render(array('class' => "chgtDenom bsswitch", "data-preleve-adherent" => "$lot->numero_dossier", "data-preleve-lot" => "$lot->numero_logement_operateur",'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                      <?php else: ?>
                          <span>Dégustable&nbsp;:&nbsp;&nbsp;</span>
                          <span class="<?php if($lot->affectable):?> glyphicon glyphicon-ok-sign <?php else:?>glyphicon glyphicon-remove <?php endif; ?>"></span>
                      <?php endif; ?>
                    </div>
                </div>
              <?php else: ?>
                <div style="margin-bottom: 0;" class="">
                  <div class="col-xs-12">
                      <span>Dégustable&nbsp;:&nbsp;&nbsp;</span>
                      <span class="<?php if($lot->affectable):?> glyphicon glyphicon-ok-sign <?php else:?>glyphicon glyphicon-remove <?php endif; ?>"></span>
                  </div>
                </div>
              <?php endif; ?>
              </div>
            <?php endif; ?>
          </td>
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
              <h4 class="modal-title" id="myModalLabel">Modification du logement <strong><?php echo $lot->numero_logement_operateur ?></strong></h4>
            </div>
            <div class="modal-body">
              <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php echo $form['numero_logement_operateur']->renderLabel("Nouveau logement", array('class' => "col-sm-4 control-label")); ?>
                            <div class="col-sm-8">
                                  <?php echo $form['numero_logement_operateur']->render(); ?>
                            </div>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Fermer</button>
              <button type="submit" class="btn btn-success pull-right">Enregistrer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>
<?php endforeach; ?>
