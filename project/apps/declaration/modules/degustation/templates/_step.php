<?php 
$etapes = DegustationEtapes::getInstance();
$etapeCourante = ($degustation->exist('etape') && $degustation->etape)? $degustation->etape : $etapes->getFirst();
?>
<ol class="breadcrumb-steps">
    <li class="<?php if($active == DegustationEtapes::ETAPE_OPERATEURS): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, DegustationEtapes::ETAPE_OPERATEURS)): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_operateurs", $degustation) ?>">Opérateurs</a>
        </div>
    </li>
    <li class="<?php if($active == DegustationEtapes::ETAPE_DEGUSTATEURS): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, DegustationEtapes::ETAPE_DEGUSTATEURS)): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_degustateurs", $degustation) ?>">Dégustateurs</a>
        </div>
    </li>
    <li class="<?php if($active == DegustationEtapes::ETAPE_AGENTS): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, DegustationEtapes::ETAPE_AGENTS)): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_agents", $degustation) ?>">Agents</a>
        </div>
    </li>
    <li class="<?php if($active == DegustationEtapes::ETAPE_PRELEVEMENTS): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, DegustationEtapes::ETAPE_PRELEVEMENTS)): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_prelevements", $degustation) ?>">Prélevements</a>
        </div>
    </li>
    <li class="<?php if($active == DegustationEtapes::ETAPE_VALIDATION): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, DegustationEtapes::ETAPE_VALIDATION)): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_validation", $degustation) ?>">Validation</a>
        </div>
    </li>
</ol>
