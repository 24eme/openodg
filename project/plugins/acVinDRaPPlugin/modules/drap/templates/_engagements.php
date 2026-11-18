<?php if (ParcellaireConfiguration::getInstance()->hasEngagements() && count($validation->getEngagements()) > 0) : ?>
    <br/>
        <h2 class="h3">Je m'engage à :</h2>
        <div class="alert" role="alert" id="engagements"  style="padding-top:0;">
            <div class="form-group">
                <?php foreach ($validation->getEngagements() as $engagement): ?>
                    <div class="checkbox-container <?php if ($form['engagement_' . $engagement->getCode()]->hasError()): ?> has-error <?php endif; ?>">
                        <?php if ($form['engagement_' . $engagement->getCode()]->getError()): ?>
                            <div class="alert alert-danger" role="alert"> <?php echo $form['engagement_' . $engagement->getCode()]->renderError(); ?></div>
                        <?php endif; ?>
                        <div class="checkbox">
                            <label>
                                <?php echo $form['engagement_' . $engagement->getCode()]->render(); ?>
                                <?php echo $engagement->getMessage(); ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    <?php endif ?>
