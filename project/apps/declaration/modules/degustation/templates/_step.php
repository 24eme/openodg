<?php 
$etapes = TourneeEtapes::getInstance();
$etapeCourante = ($tournee->exist('etape') && $tournee->etape)? $tournee->etape : $etapes->getFirst();
?>
<ol class="breadcrumb-steps">
    <li class="<?php if($active == TourneeEtapes::ETAPE_OPERATEURS): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, TourneeEtapes::ETAPE_OPERATEURS)): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_operateurs", $tournee) ?>" class="ajax">Opérateurs</a>
        </div>
    </li>
    <li class="<?php if($active == TourneeEtapes::ETAPE_DEGUSTATEURS): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, TourneeEtapes::ETAPE_DEGUSTATEURS)): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_degustateurs", $tournee) ?>" class="ajax">Dégustateurs</a>
        </div>
    </li>
    <li class="<?php if($active == TourneeEtapes::ETAPE_AGENTS): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, TourneeEtapes::ETAPE_AGENTS)): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_agents", $tournee) ?>" class="ajax">Agents</a>
        </div>
    </li>
    <li class="<?php if($active == TourneeEtapes::ETAPE_PRELEVEMENTS): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, TourneeEtapes::ETAPE_PRELEVEMENTS)): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_prelevements", $tournee) ?>" class="ajax">Prélevements</a>
        </div>
    </li>
    <li class="<?php if($active == TourneeEtapes::ETAPE_VALIDATION): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeCourante, TourneeEtapes::ETAPE_VALIDATION)): ?>visited<?php endif; ?>">
        <div class="step">
            <a href="<?php echo url_for("degustation_validation", $tournee) ?>" class="ajax">Validation</a>
        </div>
    </li>
</ol>
