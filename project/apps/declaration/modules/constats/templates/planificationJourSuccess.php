<?php
use_helper("Date");
$nbTotalAgent = "X";
$pourcentRealises = "0";
$nbRaisins = "0";
$nbVins = "0";
?>
<div class="page-header">
    <h2>Constats Jours</h2>    
</div>
<div class="row">
    <div class="col-xs-8 col-xs-offset-2">
        <div class="row">
            <div class="col-xs-2">
                <a href="<?php echo url_for('constats_planification_jour', array('jour' => Date::addDelaiToDate("-1 day", $jour))); ?>">
                    <span class="glyphicon glyphicon-arrow-left"></span>
                </a>
            </div>
            <div class="col-xs-6">
                <h4><?php echo format_date($jour, "P", "fr_FR"); ?></h4>
            </div>
            <div class="col-xs-2">
                <a href="<?php echo url_for('constats_planification_jour', array('jour' => Date::addDelaiToDate("+1 day", $jour))); ?>">
                    <span class="glyphicon glyphicon-arrow-right"></span>
                </a>

            </div>
        </div>
    </div>

</div>

<div class="row row-margin">
    <table class="table table-hover table-bordered">
        <tr>
            <th><?php echo $nbTotalAgent ?> Agents</th>
            <th><?php echo $pourcentRealises ?>% Réalisé </th>
            <th><?php echo $nbRaisins ?>&nbsp;<span class="icon-raisins size-36"></span>&nbsp;&nbsp;<?php echo $nbVins ?>&nbsp;<span class="icon-mouts size-36"></span></th>
            <th></th>
        </tr>

    </table>
</div>
<div class="row row-margin row-button">    
        <a class="btn btn-default" href="<?php echo url_for('constats_planification_ajout_agent', array('jour' => $jour)) ?>">Ajouter un agent</a>
</div>



