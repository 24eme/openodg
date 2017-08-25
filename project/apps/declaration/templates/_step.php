<?php
$etapeMax = ($object->exist('etape') && $object->etape)? $object->etape : $object->getFirst();
$stepNum = $etapes->getEtapeNum($etapeMax);
?>
<nav class="navbar navbar-default ">
    <ul class="nav navbar-nav">
    <?php foreach ($etapes->getEtapesHash() as $k => $num): ?>
        <?php $actif = ($step == $k); ?>
        <?php $past = ($etapes->isGt($etapeMax, $k)); ?>
        <li class="<?php if($actif): ?>active<?php endif; ?> <?php if (!$past && !$actif): ?>disabled<?php endif; ?> <?php if ($past && !$actif): ?>visited<?php endif; ?>">
                <a href="<?php
    if (isset($routeparams) && isset($routeparams[$etapes->getRouteLink($k)])) {
       echo url_for($etapes->getRouteLink($k), $routeparams[$etapes->getRouteLink($k)]->getRawValue());
    }else{
       echo url_for($etapes->getRouteLink($k), $object);
    }
    ?>" class="<?php if($stepNum <= 1): ?>ajax<?php endif; ?>"><?php echo $etapes->getLibelle($k, ESC_RAW);?></a>
        </li>
    <?php endforeach; ?>
    </ul>
</nav>
