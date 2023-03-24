<?php
$etapeMax = ($object->exist('etape') && $object->etape)? $object->etape : null;
$stepNum = $etapes->getEtapeNum($etapeMax);
?>
<nav class="navbar navbar-default nav-step">
    <ul class="nav navbar-nav">
    <?php foreach ($etapes->getEtapesHash() as $k => $num):
      ?>
        <?php $actif = ($step == $k); ?>
        <?php $past = ($etapes->isGt($etapeMax, $k)); ?>
        <?php $disabled = ($etapes->isEtapeDisabled($k, $object)); ?>
        <li style="<?php if($disabled): ?>opacity: 0.7;<?php endif; ?>" class="<?php if($actif): ?>active<?php endif; ?> <?php if ((!$past && !$actif) || $disabled): ?>disabled<?php endif; ?> <?php if ($past && !$actif): ?>visited<?php endif; ?>">
                <a href="<?php
    if (isset($routeparams) && isset($routeparams[$etapes->getRouteLink($k)])) {
       echo url_for($etapes->getRouteLink($k), $routeparams[$etapes->getRouteLink($k)]->getRawValue());
    }else{
       echo url_for($etapes->getRouteLink($k), $object);
    }
    ?>" class="<?php echo strtolower($k); ?> <?php if(isset($ajax) && $ajax): ?>ajax<?php endif; ?>"><?php echo str_replace('%campagne%', intval($object->campagne) - 1, $etapes->getLibelle($k, $object, ESC_RAW));?></a>
        </li>
    <?php endforeach; ?>
    </ul>
</nav>
