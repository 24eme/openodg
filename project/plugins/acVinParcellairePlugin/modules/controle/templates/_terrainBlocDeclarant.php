<h4 class="strong">{{ controleCourant.declarant.nom }}</h4>

<p>
<a href="mailto:{{ controleCourant.declarant.email }}">{{ controleCourant.declarant.email }}</a><br />
<a href="callto:{{ controleCourant.declarant.telephone_bureau }}">{{ controleCourant.declarant.telephone_bureau }}</a><br /><a href="callto:{{ controleCourant.declarant.telephone_mobile }}">{{ controleCourant.declarant.telephone_mobile }}</a>
</p>
<br/>
<div v-if="controleCourant.liaisons_operateurs.length">
    <h4>Cave(s) coopérative affectée(s) à cet opérateur :</h4>
    <li v-for="liaison in controleCourant.liaisons_operateurs" style="list-style-type: none">
        {{ liaison.libelle_etablissement }} - <span class="text-muted"> CVI: {{ liaison.cvi }}</span>
    </li>
</div>
<div v-else>
    <h4>Cet opérateur n'apporte pas à une cave coopérative</h4>
</div>
