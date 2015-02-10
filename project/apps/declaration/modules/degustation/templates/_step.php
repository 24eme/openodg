<ol class="breadcrumb-steps">
    <li class="<?php if ($active == 'operateurs'): ?>active<?php endif; ?> <?php if (in_array($active, array('degustation', 'degustateurs', 'agents', 'prelevements', 'validation'))): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_operateurs", $degustation) ?>">Opérateurs</a>
        </div>
    </li>
    <li class="<?php if ($active == 'degustateurs'): ?>active<?php endif; ?> <?php if (in_array($active, array('agents', 'prelevements', 'validation'))): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_degustateurs", $degustation) ?>">Dégustateurs</a>
        </div>
    </li>
    <li class="<?php if ($active == 'agents'): ?>active<?php endif; ?> <?php if (in_array($active, array('prelevements', 'validation'))): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_agents", $degustation) ?>">Agents</a>
        </div>
    </li>
    <li class="<?php if ($active == 'prelevements'): ?>active<?php endif; ?> <?php if (in_array($active, array('validation'))): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_prelevements", $degustation) ?>">Prélevements</a>
        </div>
    </li>
    <li class="<?php if ($active == 'validation'): ?>active<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_validation") ?>">Validation</a>
        </div>
    </li>
</ol>
