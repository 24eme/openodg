<?php 
$etapes = DrevEtapes::getInstance();
$etapeCourante = ($drev->exist('etape') && $drev->etape)? $drev->etape : $etapes->getFirst();
$steps = array(
                    "exploitation" => 1,
                    "revendication" => 2,
                    "degustation_conseil" => 3,
                    "controle_externe" => 4,
                    "validation" => 5,
                    "confirmation" => 6,
                      ); ?>
<?php $stepNum = isset($steps[$step]) ? $steps[$step] : 0; ?>

<ol class="breadcrumb-steps">
    <li class="<?php if($stepNum == 1): ?>active<?php endif; ?> <?php if($stepNum > 1): ?>visited<?php endif; ?>">
        <div class="step">
        	<?php if($etapes->isGt($etapeCourante, DrevEtapes::ETAPE_EXPLOITATION)): ?>
            <a href="<?php echo url_for("drev_exploitation", $drev) ?>" class="<?php if($stepNum <= 1): ?>ajax<?php endif; ?>">Exploitation</a>
            <?php else: ?>
            <span>Exploitation</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 2): ?>active<?php endif; ?> <?php if($stepNum > 2): ?>visited<?php endif; ?>">
        <div class="step">
        	<?php if($etapes->isGt($etapeCourante, DrevEtapes::ETAPE_REVENDICATION)): ?>
            <a href="<?php echo url_for("drev_revendication", $drev) ?>" class="<?php if($stepNum <= 2): ?>ajax<?php endif; ?>">Revendication</a>
            <?php else: ?>
            <span>Revendication</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 3): ?>active<?php endif; ?> <?php if($stepNum > 3): ?>visited<?php endif; ?>">
        <div class="step">
        	<?php if($etapes->isGt($etapeCourante, DrevEtapes::ETAPE_DEGUSTATION)): ?>
            <a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>" class="<?php if($stepNum <= 3): ?>ajax<?php endif; ?>">Dégustation conseil</a>
            <?php else: ?>
            <span>Dégustation conseil</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 4): ?>active<?php endif; ?> <?php if($stepNum > 4): ?>visited<?php endif; ?>">
        <div class="step">
        	<?php if($etapes->isGt($etapeCourante, DrevEtapes::ETAPE_CONTROLE)): ?>
            <a href="<?php echo url_for("drev_controle_externe", $drev) ?>" class="<?php if($stepNum <= 4): ?>ajax<?php endif; ?>">Contrôle externe</a>
            <?php else: ?>
            <span>Contrôle externe</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 5): ?>active<?php endif; ?> <?php if($stepNum > 5): ?>visited<?php endif; ?>">
        <div class="step">
        	<?php if($etapes->isGt($etapeCourante, DrevEtapes::ETAPE_VALIDATION)): ?>
            <a href="<?php echo url_for("drev_validation", $drev) ?>" class="<?php if($stepNum <= 5): ?>ajax<?php endif; ?>">Validation</a>
            <?php else: ?>
            <span>Validation</span>
            <?php endif; ?>
        </div>
    </li>
</ol>
