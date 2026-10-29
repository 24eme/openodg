<?php use_helper('Date') ?>
<?php if (ParcellaireConfiguration::getInstance()->hasDeclarationsLiees()): ?>
<p class="text-center">Déclarations <?php echo $obj->periode ?> relatives à vos parcelles</p>
<div class="row">
    <?php
    foreach ($parcellairesLies as $type => $item):
        $parcellaireLie = $item['obj'];
    ?>
    <div class="col-sm-6 col-md-4 col-xs-12">
        <div style="margin-bottom:0px;" class="panel panel-<?php if ($parcellaireLie && $parcellaireLie->isValidee()): ?>success<?php elseif($parcellaireLie && !$parcellaireLie->isValidee()): ?>warning<?php else: ?>default<?php endif; ?>">
            <div class="panel-heading">
                <h3 class="panel-title"><span class="glyphicon glyphicon-<?php echo ($parcellaireLie && $parcellaireLie->isValidee())? 'check' : 'unchecked'; ?>"></span> <?php echo $item['libelle']?></h3>
            </div>
            <div class="panel-body">
                <?php if ($parcellaireLie && $parcellaireLie->isValidee()): ?>
                    <a class="btn btn-xs btn-block btn-default" href="<?php echo url_for(strtolower($type).'_visualisation', ['id' => ($item['id'])]); ?>"style="margin-bottom:2px;">Déclaration validée le <?php echo format_date($parcellaireLie->validation, "dd/MM/yyyy", "fr_FR"); ?></a>

                <?php elseif ($parcellaireLie && !$parcellaireLie->isValidee()): ?>
                    <a href="<?php echo url_for(strtolower($type).'_edit', $parcellaireLie); ?>" class="btn btn-xs btn-block btn-default">Continuer la déclaration</a>
                <?php else: ?>
                    <a href="<?php echo url_for(strtolower($type).'_create', ['identifiant' => $obj->identifiant, 'periode' => $obj->periode]); ?>" class="btn btn-xs btn-block btn-default">Démarrer la déclaration</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
