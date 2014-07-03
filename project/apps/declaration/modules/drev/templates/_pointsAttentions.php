<?php if($validation->hasErreurs()): ?>
<ul class="bg-danger" style="padding: 10px 10px 10px 30px;">
	<?php foreach ($validation->getPoints(DrevValidation::TYPE_ERROR) as $controle): ?>
	<li style="padding: 3px;">
            <?php if($controle->getRawValue()->getLien() && $controle->getRawValue()->getInfo()) :?>
            <?php echo $controle->getRawValue()->getMessage() ?> : <a href="<?php echo $controle->getRawValue()->getLien() ?>">
            <?php echo $controle->getRawValue()->getInfo() ?></a>
            <?php elseif($controle->getRawValue()->getLien()): ?>
                <a href="<?php echo $controle->getRawValue()->getLien() ?>">
                    <?php echo $controle->getRawValue()->getMessage() ?>
                </a>
            <?php else: ?>
            <?php echo $controle->getRawValue() ?>
            <?php endif; ?>
	</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>
<?php if($validation->hasVigilances()): ?>
<ul class="bg-warning" style="padding: 10px 10px 10px 30px;">
	<?php foreach ($validation->getPoints(DrevValidation::TYPE_WARNING) as $controle): ?>
	<li style="padding: 3px;">
            <?php if($controle->getRawValue()->getLien() && $controle->getRawValue()->getInfo()) :?>
            <?php echo $controle->getRawValue()->getMessage() ?> : <a href="<?php echo $controle->getRawValue()->getLien() ?>">
            <?php echo $controle->getRawValue()->getInfo() ?></a>
            <?php elseif($controle->getRawValue()->getLien()): ?>
                <a href="<?php echo $controle->getRawValue()->getLien() ?>">
                    <?php echo $controle->getRawValue()->getMessage() ?>
                </a>
            <?php else: ?>
            <?php echo $controle->getRawValue() ?>
            <?php endif; ?>
	</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>