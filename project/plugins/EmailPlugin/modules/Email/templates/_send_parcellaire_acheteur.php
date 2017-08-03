Bonjour,
         
Le viticulteur <?php echo $parcellaire->declarant->nom ?> a souhaité vous faire parvenir sa déclaration d'<?php echo ($parcellaire->isIntentionCremant())? 'intention de production' : 'affectation parcellaire'; ?><?php if($parcellaire->isParcellaireCremant()): ?><?php if($parcellaire->isIntentionCremant()): ?> AOC Crémant d'Alsace<?php else: ?> Crémant<?php endif; ?><?php endif; ?> vous concernant pour l'année <?php echo $parcellaire->campagne ?>.
    
Vous trouverez ce document en pièce jointe aux formats PDF et CSV.

Bien cordialement,

Le service Appui technique (via l'application de télédéclaration)