<?php $steps = array(
                    "exploitation" => 1,
                    "revendication" => 2,
                    "degustation_conseil" => 3,
                    "controle_externe" => 4,
                    "validation" => 5,
                      ); ?>
<?php $stepNum = $steps[$step]; ?>

<ol class="breadcrumb">
    <li class="<?php if($stepNum == 1): ?>active<?php endif; ?>">1. <?php if($stepNum > 1): ?><a href="<?php echo url_for("drev_revendication", $drev) ?>">Exploitation</a><?php else: ?>Exploitation<?php endif; ?></li>
    <li class="<?php if($stepNum == 2): ?>active<?php endif; ?>">2. <?php if($stepNum > 2): ?><a href="<?php echo url_for("drev_revendication", $drev) ?>">Revendication</a><?php else: ?>Revendication<?php endif; ?></li>
    <li class="<?php if($stepNum == 3): ?>active<?php endif; ?>">3. <?php if($stepNum > 3): ?><a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>">Dégustation Conseil</a> <?php else: ?>Dégustation Conseil<?php endif; ?></li>
    <li class="<?php if($stepNum == 4): ?>active<?php endif; ?>">4. <?php if($stepNum > 4): ?><a href="<?php echo url_for("drev_controle_externe", $drev) ?>">Contrôle externe</a><?php else: ?>Contrôle externe<?php endif; ?></li>
    <li class="<?php if($stepNum == 5): ?>active<?php endif; ?>">5. <?php if($stepNum > 5): ?><a href="<?php echo url_for("drev_validation", $drev) ?>">Validation</a><?php else: ?>Validation<?php endif; ?></li>
</ol>
