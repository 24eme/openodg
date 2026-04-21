<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Text') ?>
<?php use_helper('Lot') ?>
<?php use_helper('Float') ?>

<style>
</style>
<p><strong><?php echo $controle->declarant->raison_sociale ?></strong></p>
<p>N° SIRET : <?php echo $controle->declarant->siret ?>&nbsp;&nbsp;&nbsp;N° CVI : <?php echo $controle->declarant->cvi ?></p>
<br/><br/>
<p>Parcelles controlées :</p>
<ul>
<?php foreach ($parcellaire->getParcelles() as $parcelle): ?>
<li>
    <?php echo $parcelle->commune. ' ' .$parcelle->section.' '.$parcelle->numero_parcelle; ?> (<?php echo $parcelle->cepage; ?> <?php echoFloat($parcelle->superficie); ?> ha - <?php echo $parcelle->campagne_plantation;?>)
    <ul>
    <li>Controle documentaire</li>
    <?php if ($controle->parcelles->exist($parcelle->parcelle_id)): ?>
    <li>Controle terrain :
        <ul>
            <?php foreach($controle->parcelles[$parcelle->parcelle_id]->controle->points as $point): if ($point->conformite): ?>
                <li>
                <?php echo $point->libelle; ?> :
                <?php if ($point->conformite == 'C') :?>
                    Conforme
                <?php elseif ($point->conformite == 'NO') :?>
                    Non Observable
                <?php elseif ($point->conformite == 'NC') :?>
                    Non conforme
                    <ul>
                        <?php foreach ($point->constats as $constat): ?>
                            <?php echo $constat->libelle; ?>
                            <?php if ($constat->observations): ?>
                                (<?php echo $constat->observations; ?>)
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                </li>
            <?php endif; endforeach; ?>
        </ul>
    </li>
    <?php endif; ?>
    </ul>
</li>
<?php endforeach; ?>
</ul>
