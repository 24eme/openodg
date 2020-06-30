Transferts des documents douaniers type DR/SV12/DRev pour facturation
============

# Principe et but

L'objectif est de pouvoir **éditer des factures** dans une instance d'OpenODG que nous appelerons **intance B**.

Pour ce faire, nous considérerons que les factures sont basées sur les données des documents **DRev, DR et SV12**. Mais d'autres documents pourraient être utilisés.

Nous considérerons que ces **documents** sont **présents et édités** dans une instance tierce d'OpenOdg configurée en multi-odg que nous appelerons **intance A**.

- Le principe général est alors d'**exporter** les documents douaniers DRev, DR et SV12 dans l'**instance A** mais limité au seuls produits de l'odg concernée.

- Puis une seconde étape consiste à venir **récupérer les documents** depuis l'**instance B**.

- Enfin la dernière étape consiste à procéder à la **facturation** dans l'**instance B**.

# Etape 1 : extraction depuis l'instance A

L'extraction des données dans l'instance A pour l'ensemble des syndicats configurés se fait avec les lignes de commande suivante :

 > cd openoodg/project

 > bash bin/export_by_odg.sh apps/instance_name/config/drev.yml

Si l'application est bien configurée, cela aura pour effet de produire des sous dossiers dans web/exports portant les noms des différents syndicats du projet.

## Ensemble de fichier d'export
Un ensemble de fichiers d'export sera généré dans chacun d'eux. Ces fichiers sont les suivants :
- 2019_dr_douane.csv  
- dr.csv  
- drev.csv  
- pieces.csv  
- sv11.csv  
- sv12.csv

## Cas des DRev

Le fichier drev.csv exporte l'ensemble des lignes de produits de la DRev. Ainsi si la DRev est à néant aucune ligne ne sera exportée.

De plus les DRev peuvent avoir des **modificatrices**. L'export ne comprendra que **les lignes de modificatrices** de plus haut niveau.

Enfin, il est important de noter que le fichier comprend aussi les DRev "Brouillon" et que les colonnes "Date de validation Déclarant" et	"Date de validation ODG" permetteront de déterminer le statut de la DRev.

## Cas des autres documents douaniers
Les DR/SV11 et SV12 exportées sont exportés s'il existe une DRev présente dans le fichier drev.csv pour cet opérateur.


# Etape 2 : intégration dans l'instance B

L'import des données de l'instance A vers l'instance B se fait à partir de l'instance B avec les lignes de commande suivante :

> cd openoodg/project

> bash bin/sync_from_remote_opendog.sh

Si l'application est bien configurée, cela aura pour effet d'importer les nouvelles DRev, DR et SV12 non importées.

### Cas des DRev

 Il faut noter que lors de cet import seul les **DRev validées** (non brouillons) par l'ODG et par le ressortissant seront importées.

 Ainsi si l'une des deux colonnes "Date de validation Déclarant" et	"Date de validation ODG" n'est pas remplie la DRev ne sera pas intégrer dans l'instance B.


# Tableau récapitulatif

<table>
  <tr><th colspan=5>Instance A</th><th> > </th><th colspan=2>Instance B</th></tr>
  <tr>
    <th> DRev existe </th>
    <th> Brouillon </th>
    <th> Validée </th>
    <th> Validée ODG </th>
    <th> DR/SV11/SV12 </th>
    <th> > </th>
    <th> DRev </th>
    <th> DR/SV11/SV12 </th>
  </tr>
  <tr>
  </tr>
  <tr><td>&&</td><td>Logical and</td></tr>
  <tr><td>||</td><td>Logical or</td></tr>
  <tr><td>!</td><td>Logical not</td></tr>
  <tr><td>? :</td><td>Logical ternary</td></tr>
</table>

|  |  |  |  |  | > | DRev |  |
| ------------| :-------: | :-----: | :----------: | :-: | :----: | :----: | :-: |
| X  | X |  |  | | | |  |
|   |  |  |  | X | | |  |
| X  |  | X | X | X | | X | X |
| X  |  | X | X | | | X | |
| X  | X |  |  | X | | | X |
