<h3 style="color: black">{{ controleCourant.declarant.nom }}</h3>

<p>
<a href="mailto:{{ controleCourant.declarant.email }}">{{ controleCourant.declarant.email }}</a><br />
<a href="callto:{{ controleCourant.declarant.telephone_bureau }}">{{ controleCourant.declarant.telephone_bureau }}</a><br /><a href="callto:{{ controleCourant.declarant.telephone_mobile }}">{{ controleCourant.declarant.telephone_mobile }}</a>
</p>
<div v-if="controleCourant.liaisons_operateurs[0]">
    <p>Cave(s) coopérative affectée(s) à cet opérateur :</p>
    <ul>
        <li v-for="liaison in controleCourant.liaisons_operateurs" style="list-style-type: none">
            {{ liaison.libelle_etablissement }} - <span class="text-muted"> CVI: {{ liaison.cvi }}</span>
        </li>
    </ul>
</div>
<div v-else>
    <p>Cet opérateur n'apporte pas à une cave coopérative</p>
</div>
