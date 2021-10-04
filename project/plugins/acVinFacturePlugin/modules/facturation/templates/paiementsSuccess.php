<?php use_helper('Date'); ?>
<?php use_helper('Float'); ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('facturation'); ?>">Facturation</a></li>
  <li><a href="<?php echo url_for('facturation_declarant', $facture->getCompte()); ?>"><?php echo $facture->getCompte()->getNomAAfficher() ?> (<?php echo $facture->getCompte()->getIdentifiantAAfficher() ?>)</a></li>
  <li class="active"><a href="">Paiement de la facture n°<?php echo $facture->numero_odg ?></a></li>
</ol>

<div class="page-header">
    <h2>Paiement de la facture n°<?php echo $facture->numero_odg ?> <small> du <?php echo format_date($facture->date_facturation, "dd/MM/yyyy", "fr_FR"); ?></small></h2>
</div>

<form action="" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
      <div class="form-group">
          <label class="col-xs-4 control-label">Montant à payer</label>
          <div class="col-xs-8">
              <div class="form-control-static"><?php echo echoFloat($facture->total_ttc); ?> € TTC</div>
          </div>
          <label class="col-xs-4 control-label">Restant à payer</label>
          <div class="col-xs-8">
              <div class="form-control-static"><?php echo echoFloat($facture->total_ttc - $facture->montant_paiement); ?> €</div>
          </div>
      </div>
    </div>
    <?php foreach($facture->paiements as $p): if ($p->versement_comptable):?>
        <hr/>
        <div class="row">
          <div class="form-group col-xs-12">
            <div class="col-xs-3 form-control-static">
              <strong><?php echo "Reglement numéro ".($p->getKey()+1) ?></strong>
            </div>
            <div class="col-xs-9">
              <div class="form-group">
                  <label class="col-xs-4 control-label">Montant</label>
                  <div class="col-xs-8 form-control-static">
                      <p><?php echo $p->montant; ?> €</p>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-xs-4 control-label">Date</label>
                  <div class="col-xs-8 form-control-static">
                      <?php echo $p->date; ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-xs-4 control-label">Type de règlement</label>
                  <div class="col-xs-8 form-control-static">
                      <?php echo $p->type_reglement; ?>
                  </div>
             </div>
              <div class="form-group">
              <label class="col-xs-4 control-label">Commentaire</label>
              <div class="col-xs-8 form-control-static">
                  <?php echo $p->commentaire; ?>
              </div>
            </div>
            <div class="form-group">
            <label class="col-xs-4 control-label">Versé comptablement</label>
            <div class="col-xs-8 form-control-static">Oui</div>
          </div>
            </div>
          </div>
        </div>
    <?php endif; endforeach; ?>
    <?php foreach($form['paiements'] as $key_paiement => $paiementForm): ?>
      <hr/>
          <div class="row">
            <div class="form-group col-xs-12">
              <div class="col-xs-3">
                <strong><?php echo "Reglement numéro ".($key_paiement+1) ?></strong>
              </div>
              <div class="col-xs-9">
                <div class="form-group <?php if($paiementForm["montant"]->hasError()): ?>has-error<?php endif; ?>">
                    <?php echo $paiementForm["montant"]->renderError(); ?>
                    <?php echo $paiementForm["montant"]->renderLabel("Montant du paiement", array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-5">
                        <div class="input-group">
                            <?php echo $paiementForm["montant"]->render(array("data-allow-negative" => "1")); ?>
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-euro"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group <?php if($paiementForm["date"]->hasError()): ?>has-error<?php endif; ?>">
                    <?php echo $paiementForm["date"]->renderError(); ?>
                    <?php echo $paiementForm["date"]->renderLabel("Date du paiement", array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-5">
                        <div class="input-group date-picker-week">
                            <?php echo $paiementForm["date"]->render(array("class" => "form-control")); ?>
                            <div class="input-group-addon">
                                <span class="glyphicon-calendar glyphicon"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group <?php if($paiementForm["type_reglement"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $paiementForm["type_reglement"]->renderError(); ?>
                <?php echo $paiementForm["type_reglement"]->renderLabel("Type de réglement", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <?php echo $paiementForm["type_reglement"]->render(array("class" => "form-control")); ?>
                </div>
               </div>
                <div class="form-group <?php if($paiementForm["commentaire"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $paiementForm["commentaire"]->renderError(); ?>
                <?php echo $paiementForm["commentaire"]->renderLabel("Commentaire", array("class" => "col-xs-4 control-label", "rows" => 15)); ?>
                <div class="col-xs-8">
                    <?php echo $paiementForm["commentaire"]->render(array("class" => "form-control")); ?>
                </div>
              </div>
              </div>
            </div>
          </div>
    <?php endforeach; ?>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('facturation_declarant', array("id" => "COMPTE-".$facture->identifiant)) ?>" class="btn btn-danger btn-lg btn-upper">Annuler</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Valider</a>
        </div>
    </div>
</form>
