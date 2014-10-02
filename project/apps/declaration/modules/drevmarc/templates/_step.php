<?php $steps = array(
                    "exploitation" => 1,
                    "revendication" => 2,
                    "validation" => 3,
                    "confirmation" => 4,
                      ); ?>
<?php $stepNum = isset($steps[$step]) ? $steps[$step] : 0; ?>

<ol class="breadcrumb-steps">
    <li class="<?php if($stepNum == 1): ?>active<?php endif; ?>  <?php if($stepNum > 1): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("drevmarc_exploitation", $drevmarc) ?>" class="ajax">Exploitation</a>
        </div>
    </li>
    <li class="<?php if($stepNum == 2): ?>active<?php endif; ?> <?php if($stepNum > 2): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("drevmarc_revendication", $drevmarc) ?>" class="ajax">Revendication</a>
        </div>
    </li>
    <li class="<?php if($stepNum == 3): ?>active<?php endif; ?> <?php if($stepNum > 3): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("drevmarc_validation", $drevmarc) ?>" class="ajax">Validation</a>
        </div>
    </li>
</ol>
