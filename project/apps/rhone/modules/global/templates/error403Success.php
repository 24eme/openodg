<div class="page-header no-border">
    <h2><span class="glyphicon glyphicon-minus-sign"></span>&nbsp;&nbsp;Vous n'avez pas l'autorisation d'accéder à cette page</h2>
</div>

<div class="row">
    <div class="col-xs-12">
        <p>
        Vous n'êtes pas autorisé à accéder à cette page.
        </p>
        <p>
        Si vous provenez de la plateforme vinsalsace.pro il est possible que votre compte n'ait pas encore été créé. Dans ce cas n'hésitez pas à nous contacter.
        </p>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-6">
        <?php if(($sf_user->isAuthenticated() && ($sf_user->getCompte())) || !$sf_user->isAuthenticated()): ?>
            <a href="<?php echo url_for('accueil') ?>" class="btn btn-sm btn-primary btn-upper btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour à l'accueil</a>
        <?php endif; ?>
        <a style="margin-top: 10px;" href="javascript:history.back()" class="btn btn-default btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner à la page précédente</a>
    </div>
</div>
