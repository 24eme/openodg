<?php 
$etapes = TirageEtapes::getInstance();
$etapeCourante = ($tirage->exist('etape') && $tirage->etape)? $tirage->etape : $etapes->getFirst();
$steps = array(
                    "exploitation" => 1,
                    "vin" => 2,
                    "lots" => 3,
                    "validation" => 4,
                    "confirmation" => 5,
                      ); ?>
<?php $stepNum = isset($steps[$step]) ? $steps[$step] : 0; ?>

<ol class="breadcrumb-steps">
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
            <span>Vin</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 2): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, TirageEtapes::ETAPE_LOTS)): ?>visited<?php endif; ?>">
        <div class="step">
            <?php if($etapes->isGt($etapeCourante, TirageEtapes::ETAPE_LOTS) && !$tirage->isValide()): ?>
            <a href="<?php echo url_for("tirage_lots") ?>" class="ajax">Lot</a>
            <?php else: ?>
            <span>Lot</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 3): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, TirageEtapes::ETAPE_VALIDATION)): ?>visited<?php endif; ?>">
        <div class="step">
            <?php if($etapes->isGt($etapeCourante, TirageEtapes::ETAPE_VALIDATION) && !$tirage->isValide()): ?>
            <a href="<?php echo url_for("tirage_validation", $tirage) ?>" class="ajax">Validation</a>
            <?php else: ?>
            <span>Validation</span>
            <?php endif; ?>
        </div>
    </li>
</ol>
