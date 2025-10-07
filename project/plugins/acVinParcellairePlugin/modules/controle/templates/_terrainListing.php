<h2>Opérateurs à contrôler</h2>
<table class="table table-bordered table-condensed table-striped tableParcellaire">
    <thead>
        <tr>
            <th class="col-xs-5">Opérateur</th>
            <th class="col-xs-5">Infos</th>
            <th class="col-xs-1 text-center">Parcelles</th>
            <th class="col-xs-1 text-center">Détail</th>
        </tr>
    </thead>
    <tbody>
        <tr v-for="(controle, key) in controles" :class="{ 'success': controle.audit && controle.audit.saisie == 1 }">
            <td>
                <strong>{{ controle.declarant.nom }}</strong> <span class="small">{{ controle.identifiant }}</span><br />
                <span class="text-muted">CVI {{ controle.declarant.cvi }} - SIRET {{ controle.declarant.siret }}</span>
            </td>
            <td>
                {{ controle.declarant.adresse }} {{ controle.declarant.code_postal }} {{ controle.declarant.commune }}<br />
                <a href="mailto:{{ controle.declarant.email }}">{{ controle.declarant.email }}</a> / <a href="callto:{{ controle.declarant.telephone_bureau }}">{{ controle.declarant.telephone_bureau }}</a> / <a href="callto:{{ controle.declarant.telephone_mobile }}">{{ controle.declarant.telephone_mobile }}</a>
            </td>
            <td class="text-center">
                {{ Object.keys(controle.parcelles).length }}
            </td>
            <td class="text-center">
                <RouterLink :to="{ name: 'operateur', params: { id: key } }"><span class="glyphicon glyphicon-search"></span></RouterLink>
            </td>
        </tr>
    </tbody>
</table>
