<?php
$etapeMax = ($object->exist('etape') && $object->etape)? $object->etape : $object->getFirst();
$stepNum = $etapes->getEtapeNum($etapeMax);
?>
<ol class="breadcrumb-steps <?php if($step == $etapes->getLast()) {echo 'breadcrumb-steps-last';}  else if($etapes->isGt($etapeMax, $etapes->getLast())) { echo ' breadcrumb-steps-visited'; }?>">
<?php foreach ($etapes->getEtapesHash() as $k => $num): ?>
    <li class="<?php if($step == $k): ?>active<?php endif; ?> <?php if($etapes->isGt($etapeMax, $k)): ?>visited<?php endif; ?>">
        <div class="step">
            <?php if($etapes->isGt($etapeMax, $k)): ?>
            <a href="<?php 
if (isset($routeparams) && isset($routeparams[$etapes->getRouteLink($k)])) {
   echo url_for($etapes->getRouteLink($k), $routeparams[$etapes->getRouteLink($k)]->getRawValue());   
}else{
   echo url_for($etapes->getRouteLink($k), $object);
}
?>" class="<?php if($stepNum <= 1): ?>ajax<?php endif; ?>"><?php echo $etapes->getLibelle($k, ESC_RAW);?></a>
            <?php else: ?>
            <span><?php echo $etapes->getLibelle($k, ESC_RAW); ?></span>
            <?php endif; ?>
        </div>
    </li>
<?php endforeach; ?>
</ol>
