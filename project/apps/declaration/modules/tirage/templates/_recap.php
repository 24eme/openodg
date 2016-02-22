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
<p>Vin de base ayant fait la fermantation malo-lactique : <strong><?php echo ($tirage->fermentation_lactique)? 'Oui' : 'Non'; ?> </strong></p>
<p>
    Lieu de stockage : <strong><?php  echo ($tirage->lieu_stockage)? $tirage->lieu_stockage : $tirage->declarant->adresse.' '.$tirage->declarant->code_postal.' '.$tirage->declarant->commune; ?></strong>
</p>
<br/>
<h2 class="h3">Composition du lot :</h2>
<ul style="list-style: disc;">
    <?php foreach ($tirage->composition as $compo): ?>
        <li ><?php echo $compo->nombre; ?>  bouteilles de <?php echo $compo->contenance; ?>&nbsp;</li>
        <?php endforeach; ?>
</ul>
<br/>
<div class="row">
    <div class="col-xs-12">
        Date de mise en bouteille :
<?php if ($tirage->date_mise_en_bouteille_debut == $tirage->date_mise_en_bouteille_fin ) {
        echo "le <strong>".format_date($tirage->date_mise_en_bouteille_debut, 'dd/MM/yyyy', 'fr_FR')."</strong>";
    }else{ 
        echo "du <strong>".format_date($tirage->date_mise_en_bouteille_debut, 'dd/MM/yyyy', 'fr_FR')."</strong>";
        echo " au <strong>".format_date($tirage->date_mise_en_bouteille_fin, 'dd/MM/yyyy', 'fr_FR')."</strong>";
    }?>
    </div>
</div>
<br/>

