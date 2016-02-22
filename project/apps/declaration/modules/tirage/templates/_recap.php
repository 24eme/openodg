<?php use_helper('Date') ?>
<?php use_helper('Orthographe') ?>
<p>
    Déclaration du lot de <strong>A.O.C. Crémant <?php echo $tirage->couleur_libelle ?></strong> (
    <?php
    $cpt = 1;
    foreach ($tirage->getCepagesSelectionnes() as $cepage):
        echo $cepage->getLibelle();
        echo ($cpt < count($tirage->getCepagesSelectionnes())) ? ', ' : ' ';
        $cpt++;
    endforeach;
    ?>
    )&nbsp;<?php echo elision('de', $tirage->millesime_libelle) ?>.</p>
<?php if ($tirage->millesime_libelle): ?>
    <p><strong>Millesime :</strong> Assemblé ( <?php echo $tirage->millesime_ventilation; ?> )</p>
<?php endif; ?>
<br/>
<p>Composition du lot :</p>
<div class="list-group">
    <?php foreach ($tirage->composition as $compo): ?>
        <div class="list-group-item">
            <?php echo $compo->nombre; ?>  bouteilles de <?php echo $compo->contenance; ?>&nbsp;</div>
        <?php endforeach; ?>
</div>

