<script id="dataJson" type="application/json">
<?php echo $sf_data->getRaw('json') ?>
</script>
<script>
    const { createWebHashHistory, createRouter, useRoute, useRouter } = VueRouter
    const { createApp } = Vue;

    const templates = [];

    <?php foreach(['operateurs'] as $template): ?>
        templates["<?php echo $template ?>"] = { template: "<?php echo str_replace(['"', "\n"], ['\"', ""], get_partial('controle/orga'.ucfirst($template))) ?>" }
    <?php endforeach; ?>

    const routes = [
      { path: '/', name: "operateurs", component: templates.operateurs },
    ]

    const router = createRouter({
      history: createWebHashHistory(),
      routes,
    })

    const app = createApp({
        data() {
        console.log(controles);
          return {
              controles: controles,
            }
        }
    })
</script>
