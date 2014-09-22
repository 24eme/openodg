<?php $steps = array(
                    "exploitation" => 1,
                    "revendication" => 2,
                    "degustation_conseil" => 3,
                    "controle_externe" => 4,
                    "validation" => 5,
                      ); ?>
<?php $stepNum = isset($steps[$step]) ? $steps[$step] : 0; ?>

<ol class="breadcrumb-steps">
    <li class="<?php if($stepNum == 1): ?>active<?php endif; ?> <?php if($stepNum > 1): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("drev_exploitation", $drev) ?>" class="ajax">Exploitation</a>
        </div>
    </li>
    <li class="<?php if($stepNum == 2): ?>active<?php endif; ?> <?php if($stepNum > 2): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("drev_revendication", $drev) ?>" class="ajax">Revendication</a>
        </div>
    </li>
    <li class="<?php if($stepNum == 3): ?>active<?php endif; ?> <?php if($stepNum > 3): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>" class="ajax">Dégustation conseil</a>
        </div>
    </li>
    <li class="<?php if($stepNum == 4): ?>active<?php endif; ?> <?php if($stepNum > 4): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("drev_controle_externe", $drev) ?>" class="ajax">Contrôle externe</a>
        </div>
    </li>
    <li class="<?php if($stepNum == 5): ?>active<?php endif; ?> <?php if($stepNum > 5): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("drev_validation", $drev) ?>" class="ajax">Validation</a>
        </div>
    </li>
</ol>
