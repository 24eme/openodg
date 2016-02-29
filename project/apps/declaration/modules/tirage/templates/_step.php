<?php 
$etapes = TirageEtapes::getInstance();
$etapeCourante = ($tirage->exist('etape') && $tirage->etape)? $tirage->etape : $etapes->getFirst();
$stepNum = isset(TirageEtapes::$etapes[$step]) ? TirageEtapes::$etapes[$step] : 0; ?>
<ol class="breadcrumb-steps<?php if($stepNum == 4) {echo ' breadcrumb-steps-last';} else if($etapes->isGt($etapeCourante, TirageEtapes::ETAPE_VALIDATION)) { echo ' breadcrumb-steps-visited'; }?>">
    <li class="<?php if($stepNum == 1): ?>active<?php endif; ?>  <?php if($etapes->isGt($etapeCourante, TirageEtapes::ETAPE_EXPLOITATION)): ?>visited<?php endif; ?>">
        <div class="step">
         <?php if($etapes->isGt($etapeCourante, TirageEtapes::ETAPE_EXPLOITATION) && !$tirage->isValide()): ?>
            <a href="<?php echo url_for("tirage_exploitation", $tirage) ?>" class="ajax">Exploitation</a>
            <?php else: ?>
            <span>Exploitation</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 2): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, TirageEtapes::ETAPE_VIN)): ?>visited<?php endif; ?>">
        <div class="step">
            <?php if($etapes->isGt($etapeCourante, TirageEtapes::ETAPE_VIN) && !$tirage->isValide()): ?>
            <a href="<?php echo url_for("tirage_vin", $tirage) ?>" class="ajax">Vin</a>
            <?php else: ?>
            <span>Caractéristiques</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 3): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, TirageEtapes::ETAPE_LOTS)): ?>visited<?php endif; ?>">
        <div class="step">
            <?php if($etapes->isGt($etapeCourante, TirageEtapes::ETAPE_LOTS) && !$tirage->isValide()): ?>
            <a href="<?php echo url_for("tirage_lots", $tirage) ?>" class="ajax">Lot</a>
            <?php else: ?>
            <span>Répartition</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 4): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, TirageEtapes::ETAPE_VALIDATION)): ?>visited<?php endif; ?>">
        <div class="step">
            <?php if($etapes->isGt($etapeCourante, TirageEtapes::ETAPE_VALIDATION) && !$tirage->isValide()): ?>
            <a href="<?php echo url_for("tirage_validation", $tirage) ?>" class="ajax">Validation</a>
            <?php else: ?>
            <span>Validation</span>
            <?php endif; ?>
        </div>
    </li>
</ol>
