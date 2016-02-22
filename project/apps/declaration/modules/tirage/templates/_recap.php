<?php use_helper('Date') ?>
<?php use_helper('Orthographe') ?>
<div class="text-center">
    <span class="lead">Crémant <?php echo $tirage->couleur_libelle ?> (
    <?php
    $cpt = 1;
    foreach ($tirage->getCepagesSelectionnes() as $cepage):
        echo $cepage->getLibelle();
        echo ($cpt < count($tirage->getCepagesSelectionnes())) ? ', ' : ' ';
        $cpt++;
    endforeach;
    ?>
        ) </span>
</div>
<div class="text-center">Millesime : <span class="lead"> <?php echo $tirage->millesime_libelle; ?> </span>
<?php if ($tirage->millesime == TirageClient::MILLESIME_ASSEMBLE): ?>
<?php echo '&nbsp;( '.$tirage->millesime_ventilation.' )'; ?>
<?php endif; ?>
</div>
<br/>
<span class="lead">Composition du lot :</span>
<ul class="list-unstyled">
    <?php foreach ($tirage->composition as $compo): ?>
        <li class="col-xs-12 col-xs-offset-1">
            <?php echo $compo->nombre; ?>  bouteilles de <?php echo $compo->contenance; ?>&nbsp;</li>
        <?php endforeach; ?>
</ul>
<br/>
<br/>
<div class="row">
    <div class="col-xs-12">
        Date de mise en bouteille :
<?php if ($tirage->date_mise_en_bouteille_debut == $tirage->date_mise_en_bouteille_fin ) {
        echo "le ".format_date($tirage->date_mise_en_bouteille_debut, 'dd/MM/yyyy', 'fr_FR');
    }else{ 
        echo "du ".format_date($tirage->date_mise_en_bouteille_debut, 'dd/MM/yyyy', 'fr_FR');
        echo " au ".format_date($tirage->date_mise_en_bouteille_fin, 'dd/MM/yyyy', 'fr_FR');
    }?>
    </div>
</div>
<br/>

