<ol class="breadcrumb-steps">
    <li class="<?php if ($active == 'creation'): ?>active<?php endif; ?> <?php if (in_array($active, array('parcelles', 'acheteurs', 'validation'))): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("parcellaire_creation",array('identifiant' => $identifiant)) ?>">Infos</a>
        </div>
    </li>
    <li class="<?php if ($active == 'parcelles'): ?>active<?php endif; ?> <?php if (in_array($active, array('acheteurs', 'validation'))): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("parcellaire_parcelles",array('identifiant' => $identifiant)) ?>">Parcelles</a>
        </div>
    </li>
    <li class="<?php if ($active == 'acheteurs'): ?>active<?php endif; ?> <?php if (in_array($active, array('validation'))): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("parcellaire_acheteurs",array('identifiant' => $identifiant)) ?>">Acheteurs</a>
        </div>
    </li>
    <li class="<?php if ($active == 'validation'): ?>active<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_validation") ?>">Validation</a>
        </div>
    </li>
</ol>
