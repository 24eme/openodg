<form method="post" action="<?php echo url_for("habilitation_export_historique"); ?>" role="form" class="form-horizontal" novalidate>
    <div class="col-xs-6 col-xs-offset-3">
        <?php echo $form->renderGlobalErrors(); ?>
        <?php echo $form->renderHiddenFields(); ?>
        <h3 class="text-center">Export de l'historique des demandes</h3>
        <br />
        <div class="form-group">
            <?php echo $form['statut']->renderLabel('Statut', array('class' => 'control-label col-xs-4')) ?>
            <div class="col-xs-6">
                <span class="text-danger"><?php echo $form['statut']->renderError() ?></span>
                <?php echo $form['statut']->render(array('data-placeholder' => "Saisir le statut", "required" => true ,"class" => "form-control select2 select2-offscreen select2autocomplete")) ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo $form['date']->renderLabel('Date', array('class' => 'control-label col-xs-4')) ?>
            <div class="col-xs-6">
                <span class="text-danger"><?php echo $form['date']->renderError() ?></span>
                <div class="input-group date-picker" style="padding-bottom:10px;">
                    <?php echo $form['date']->render(array('placeholder' => "Saisir la date", "required" => true ,"class" => "form-control")) ?>
                    <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6">
                <a href="<?php echo url_for('habilitation') ?>" class="btn btn-default">Annuler</a>
            </div>
            <div class="col-xs-4 text-right">
                <button type="submit" class="btn btn-primary">Exporter</button>
            </div>
        </div>
    </div>
</form>
