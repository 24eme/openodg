<?php use_helper("Date"); ?>
<?php use_javascript("degustation.js?201504020331", "last") ?>
<form id="form_degustateurs_presence" action="" method="post" class="form-horizontal">
    <a href="<?php echo url_for("degustation_visualisation", $tournee) ?>" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></a>
    <div class="page-header text-center">
        <h2>Liste de présence des dégustateurs<br /><small>Dégustation du <?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></small></h2>
    </div> 
    <div class="row">
        <div class="col-xs-12">
            <?php echo $form->renderHiddenFields(); ?>
            <?php echo $form->renderGlobalErrors(); ?>
            <div class="list-group">
                <?php foreach($form as $key => $field): ?>
                    <?php if($field->isHidden()): continue; endif; ?>
                    <?php $degustateur = $tournee->get($key); ?>
                    <div class="list-group-item col-xs-12">
                        <div class="col-xs-7">
                        <?php echo $degustateur->nom ?> <small class="text-muted"><?php echo $degustateur->commune ?> <?php if($degustateur->email): ?>(<?php echo $degustateur->email ?>)<?php endif; ?></small> 
                        </div>
                        <div class="col-xs-5 text-right">
                            <div class="btn-group" data-toggle="buttons">
                              <label class="btn btn-sm btn-presence non-present <?php if($degustateur->presence === 0): ?>btn-danger active<?php else: ?>btn-default btn-default-step<?php endif; ?>">
                                <input type="radio" value="0" name="<?php echo $field->renderName() ?>" autocomplete="off" <?php if($degustateur->presence === 0): ?>checked="checked"<?php endif; ?>> Non présent
                              </label>
                              <label class="btn btn-sm btn-presence ne-sais-pas <?php if($degustateur->presence === null): ?>btn-info active<?php else: ?>btn-default btn-default-step<?php endif; ?>">
                                <input type="radio" value="" name="<?php echo $field->renderName() ?>" autocomplete="off" <?php if($degustateur->presence === null): ?>checked="checked"<?php endif; ?>> Ne sais pas
                              </label>
                              <label class="btn btn-sm btn-presence present <?php if($degustateur->presence === 1): ?>btn-default active<?php else: ?>btn-default btn-default-step<?php endif; ?>">
                                <input type="radio" value="1" name="<?php echo $field->renderName() ?>" autocomplete="off" <?php if($degustateur->presence === 1): ?>checked="checked"<?php endif; ?>> Présent
                              </label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('degustation_visualisation', $tournee) ?>" class="btn btn-danger btn-lg btn-upper">Annuler</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Enregistrer</button>
        </div>
    </div>
</form>
