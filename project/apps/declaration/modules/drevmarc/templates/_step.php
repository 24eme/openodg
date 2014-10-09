<?php 
$etapes = DrevMarcEtapes::getInstance();
$etapeCourante = ($drevmarc->exist('etape') && $drevmarc->etape)? $drevmarc->etape : $etapes->getFirst();
$steps = array(
                    "exploitation" => 1,
                    "revendication" => 2,
                    "validation" => 3,
                    "confirmation" => 4,
                      ); ?>
<?php $stepNum = isset($steps[$step]) ? $steps[$step] : 0; ?>

<ol class="breadcrumb-steps">
    <li class="<?php if($stepNum == 1): ?>active<?php endif; ?>  <?php if($etapes->isGt($etapeCourante, DrevMarcEtapes::ETAPE_EXPLOITATION)): ?>visited<?php endif; ?>">
        <div class="step">
        	<?php if($etapes->isGt($etapeCourante, DrevMarcEtapes::ETAPE_EXPLOITATION)): ?>
            <a href="<?php echo url_for("drevmarc_exploitation", $drevmarc) ?>" class="ajax">Exploitation</a>
            <?php else: ?>
            <span>Exploitation</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 2): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, DrevMarcEtapes::ETAPE_REVENDICATION)): ?>visited<?php endif; ?>">
        <div class="step">
        	<?php if($etapes->isGt($etapeCourante, DrevMarcEtapes::ETAPE_REVENDICATION)): ?>
            <a href="<?php echo url_for("drevmarc_revendication", $drevmarc) ?>" class="ajax">Revendication</a>
            <?php else: ?>
            <span>Revendication</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 3): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, DrevMarcEtapes::ETAPE_VALIDATION)): ?>visited<?php endif; ?>">
        <div class="step">
        	<?php if($etapes->isGt($etapeCourante, DrevMarcEtapes::ETAPE_VALIDATION)): ?>
            <a href="<?php echo url_for("drevmarc_validation", $drevmarc) ?>" class="ajax">Validation</a>
            <?php else: ?>
            <span>Validation</span>
            <?php endif; ?>
        </div>
    </li>
</ol>
