<?php 
$etapes = ParcellaireEtapes::getInstance();
$etapeCourante = ($parcellaire->exist('etape') && $parcellaire->etape)? $parcellaire->etape : $etapes->getFirst();
$steps = array(
                    "exploitation" => 1,
                    "parcelles" => 2,
                    "acheteurs" => 3,
                    "validation" => 4,
                      ); ?>
<?php $stepNum = isset($steps[$step]) ? $steps[$step] : 0; ?>
<ol class="breadcrumb-steps">
    <li class="<?php if($stepNum == 1): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, ParcellaireEtapes::ETAPE_EXPLOITATION)): ?>visited<?php endif; ?>">
        <div class="step">
            <?php if($etapes->isGt($etapeCourante, ParcellaireEtapes::ETAPE_EXPLOITATION)): ?>
            <a href="<?php echo url_for("parcellaire_exploitation", $parcellaire) ?>" class="<?php if($stepNum <= 1): ?>ajax<?php endif; ?>">Exploitation</a>
            <?php else: ?>
            <span>Exploitation</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 2): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, ParcellaireEtapes::ETAPE_PARCELLES)): ?>visited<?php endif; ?>">
        <div class="step">
        	<?php if($etapes->isGt($etapeCourante, ParcellaireEtapes::ETAPE_PARCELLES)): ?>
            <a href="<?php echo url_for("parcellaire_parcelles", $drev) ?>" class="<?php if($stepNum <= 2): ?>ajax<?php endif; ?>">Parcelles</a>
            <?php else: ?>
            <span>Parcelles</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 3): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, ParcellaireEtapes::ETAPE_ACHETEURS)): ?>visited<?php endif; ?>">
        <div class="step">
        	<?php if($etapes->isGt($etapeCourante, ParcellaireEtapes::ETAPE_ACHETEURS)): ?>
            <a href="<?php echo url_for("parcellaire_acheteurs", $drev) ?>" class="<?php if($stepNum <= 3): ?>ajax<?php endif; ?>">Acheteurs</a>
            <?php else: ?>
            <span>Acheteurs</span>
            <?php endif; ?>
        </div>
    </li>
    <li class="<?php if($stepNum == 4): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, ParcellaireEtapes::ETAPE_VALIDATION)): ?>visited<?php endif; ?>">
        <div class="step">
        	<?php if($etapes->isGt($etapeCourante, ParcellaireEtapes::ETAPE_VALIDATION)): ?>
            <a href="<?php echo url_for("parcellaire_validation", $drev) ?>" class="<?php if($stepNum <= 4): ?>ajax<?php endif; ?>">Validation</a>
            <?php else: ?>
            <span>Validation</span>
            <?php endif; ?>
        </div>
    </li>
</ol>

