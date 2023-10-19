<div class="page-header no-border">
    <h2><span class="glyphicon glyphicon-minus-sign"></span>&nbsp;&nbsp;Vous n'avez pas l'autorisation d'accéder à cette page</h2>
</div>

<div class="row">
    <div class="col-xs-12 pt-2" style="min-height: 250px;">
        <p>
        Vous n'êtes pas autorisé à accéder à cette page<?php
        if (Organisme::getInstance()->getCurrentRegion() && isset($_ENV["Forward403Region"])) {
            echo " : l'opérateur ne semble pas <a href='".url_for('habilitation_declarant', array('identifiant' => $identifiant))."'>habilité</a> pour vos appellations ".Organisme::getInstance()->getCurrentRegion();
        }
        ?>.
       </p>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-6">
        <a style="margin-top: 10px;" href="javascript:history.back()" class="btn btn-default btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner à la page précédente</a>
    </div>
</div>
