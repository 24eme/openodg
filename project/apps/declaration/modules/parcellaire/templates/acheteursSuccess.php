<?php include_partial('step', array('parcellaire' => $parcellaire, 'step' => 'acheteurs', 'identifiant' => 'XXX')); ?>

<div class="page-header">
    <h2>Saisie des acheteurs</h2>
</div>


<form action="" method="post" class="">
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>

    <div class="row">       
        <div class="col-xs-12">
            <div id="listes_cepages" class="list-group">
                <table class="table table-striped">
                    <tr>
                        <th></th>
                        <?php foreach($form->getAcheteurs() as $libelle): ?>           
                        <th><?php echo $libelle ?></th>
                        <?php endforeach; ?>  
                    </tr>
                    <?php foreach($form as $key => $field) : ?>
                    <?php if($field->isHidden()) { continue; } ?>
                    <tr>
                        <td>
                            <?php echo $field->renderLabel() ?>
                            <?php echo $field->renderError() ?>
                        </td>
                        <?php foreach($field->getWidget()->getChoices() as $key => $option): ?>            
                        <td><input type="checkbox" id="<?php echo $field->renderId() ?>_<?php echo $key ?>" name="<?php echo $field->renderName() ?>[]" value="<?php echo $key ?>" <?php if(is_array($field->getValue()) && in_array($key, $field->getValue())): ?>checked="checked"<?php endif; ?> /></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for("parcellaire_parcelles", array('sf_subject' => $parcellaire, 'appellation' => ParcellaireClient::getInstance()->getFirstAppellation())) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
        </div>
        <div class="col-xs-6 text-right">
            <?php if ($parcellaire->exist('etape') && $parcellaire->etape == ParcellaireEtapes::ETAPE_VALIDATION): ?>
                <button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button>
            <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer <small>vers la validation</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
            <?php endif; ?>
        </div>
    </div>
</form>
