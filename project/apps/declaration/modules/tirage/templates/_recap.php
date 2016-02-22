<?php
use_helper("Date");
?>
Déclaration du lot de <strong>A.O.C. Crémant <?php echo $tirage->couleur_libelle ?></strong> (
<?php
$cpt=1;
foreach ($tirage->getCepagesSelectionnes() as $cepage):
    echo $cepage->getLibelle();
    echo ($cpt < count($tirage->getCepagesSelectionnes()))? ', ' : ' ';
    $cpt++;
endforeach;
?>
)
<br/>
de <?php echo $tirage->millesime_libelle ?>

