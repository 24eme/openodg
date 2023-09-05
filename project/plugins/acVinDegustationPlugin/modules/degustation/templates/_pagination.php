<?php
$etapes =  DegustationEtapes::getInstance();
?>
<div class="row row-button">
    <div class="col-xs-4">
        <a
            class="btn btn-default btn-upper"
            href="<?php echo url_for($etapes->getPreviousLink($active), ['id' => $degustation->_id]); ?>"
        >
            <span class="glyphicon glyphicon-chevron-left"></span> Retour
        </a>
    </div>
    <div class="col-xs-4 text-center">
    </div>
    <div class="col-xs-4 text-right">
        <a
            id="btn_suivant"
            class="btn btn-primary btn-upper"
            href="<?php echo ($is_enabled) ? url_for($etapes->getNextLink($active), ['id' => $degustation->_id])  : "#"; ?>"
            <?php if(!$is_enabled): echo 'disabled="disabled"'; endif; ?>
        >
            Valider&nbsp;<span class="glyphicon glyphicon-chevron-right"></span>
        </a>
    </div>
</div>
