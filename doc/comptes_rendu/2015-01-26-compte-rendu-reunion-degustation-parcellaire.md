Compte rendu de la réunion du 27 Janvier 2015 : *Dégustation et Parcellaire*
============================================================================

Contact
-------

### Correction

-   Tags vide
-   Pagination perd l'inactif ou non
-   Visu Réduire les infos et remonter les chais à droite de la carte
-   Ajouter les chais sur la carte
-   cvi:6823700100 OR cvi:6823700110 ne fonctionne pas
-   Mettre l'onglet contact en premier
-   Mettre le bouton archiver dans modifier
-   Afficher la clé identité

### Evolution

Chiffrage :

-   Logger les modifications de contacts (chiffrage)
-   Import ponctuel de liste à tagguer (chiffrage)
-   Lien vers une page de wiki (en point d'aide), référencant les différentes requêtes elastic search possible
-   Mailto sur plusiers email à tester (proposer en menu déroulant dans l'export CSV)

DI
--

-   Sera intégré dans le module contact

Faire un chiffrage pour :

-   Ajout d'un tag "appellation DI"
-   Pouvoir associer à un contacts des documents avec un titre, une
    date, un type de document et le document : fichier, texte, url
-   Loguer les actions des utilisateurs des contacts
-   Consultation des documents archivés (+ version vierge ?)

DRev
----

-   Case à cocher conditionneur (dans l'écran administratif)

Tournée
-------

### Organisation

Création

-   Remplacer "Date de fin de prélevements" par "Date de dégustation",
    on pourra calculer la date de fin de prélevement automatiquement
    (\\\~1 semaine avant la dégustation)
-   Ajouter "heure de dégustation"
-   Renommer "Famille" en "Appellation/Mention"

Faire un écran supplémentaire avec :

-   Date de prélévement "du ... au" (affichage)
-   Nombre d'opérateurs potentiels (affichage)
-   Nombre d'opérateur à prélever (saisie)
-   Le nombre de commission (saisie)
-   Le lieu de dégustation (éditable pour les Grands Crus)

Choix des opérateurs :

-   Libellé "prélevé le" =\\\> "prélevé en"

Cas Grands Crus :

-   Sélectionner tous les opérateurs
-   Sélectionner tous les cépages

Choix des dégustateurs :

-   Fenues : les 3 derniers mois
-   Formation : la dernière année
-   Mettre le nombre de sélectionné en tête d'onglet

Choix des agents de prélèvement :

-   En fonction de VT/AOC (avec tag "Constat VT")

Affectation des prélévèments :

-   Répartition du plus proche du domicile de l'agent préleveur jusqu'au
    CIVA
-   Une div par heure
-   Une div par opérateur
-   Si l'heure bouge , les heures \\\> bougent dans le même proportion
-   Si l'opérateur bouge, remonte les opérateurs du dessous
-   Deux par heure si controle chais sinon trois par heure
-   Mettre des couleurs par journée/agent de prélevement sur la carte
    "Tous"

Visualisation / Validation :

-   Récapitulatif de liste de dégustateurs
-   Récapitulatif des opérateurs à prelever
-   Visualisation sous forme de timeline dans le temps : tournées, dégustateurs à inviter (Avec l'info de l'existance de l'email ou non)

Envoi de mails :

-   On envoi uniquement des mails aux dégustateurs, opérateurs et agents
    de prélevement : les destinaitaires n'ayant pas de mail recevront un
    courrier à la main. Le contenu du mail sera juste du texte.

### Prélévement

-   Code d'anonminat : RI 99 + ~~code héxadécimal~~ (code d'erreur à
    trouver) = décimal
-   Volume total vignifié affiché à partir de la DR si existant sinon champ texte (le volume sera demandé à l'opérateur)
-   Coche pour indiquer que le lot n'a pas été prelevé
-   Liste des cuves (type texte) pour contrôler la répartition en cuve du volume vignifié total
-   Ajouter une coche lot non prélevable

### Dégustation

Saisie des numéros d'anonimat de dégustation (Interface adapté à une
tablette) :

-   On ne génere pas d'étiquette les numéros seront écrits sur des
    étiquettes blanche.

1.  Écran choix d'une commission
2.  Écran autocomplete saisie numéro prélèvement bouteille (que le
    nombre)
3.  Écran confirmation du numéro dégustation / prélevement / cépage

Saisie des membres commission / notes (Interface adapté à une tablette)
:

1.  Choix de la commission
2.  Choix des dégustateurs
3.  Liste des vins à déguster
4.  Note d'un vin (note qualité / défaut qualité (tous) | note matière /
    défaut matière (Grd Cru / AOC Alsace) | note typicité / défaut typicité
    (Grd Cru ) | note concentration /défaut concentration (VT) | note
    équilibre / défaut équilibre (VT) )
5.  Récap des notes 6 Fermer la dégustation pour pouvoir voir les
    numéros d'annonimats

Visualisation des commissions :

-   Choix d'une commission
-   Visualisation des notes + choix OK / OPE / VISITE (+ date / heure)

Génération d'un PDF de retour personnalisé en fonction de "OK / OPE /
VISITE" envoyé par mail (sinon le courrier est envoyé à la main : même
système que pour les mails de fin d'organisation)

Mise en place de la VM Interne pour installer le LDAP
-----------------------------------------------------

La Machine Virtuelle a été crée par Raphaël avec 20Go de disque et 2G de
RAM. Elle est bien accessible depuis l'extérieur sur la dédibox
d'Actualys et sur le serveur de Typhon.

Une fois que l'on aura deployer le LDAP, nous pourrons paramétrer chacun
des clients de messagerie pour la connexion avec le LDAP.

Parcellaire
-----------

-   Saisie de l'ensemble des acheteurs sur le premier écran
-   L'écran de saisi des superficies de cépages ne change pas
-   Ajout d'un troisième écran avant la validation pour déclarer les
    achats par produit jusqu'au niveau appellation avec en entetes les
    acheteurs et des case à cocher pour chaque produit appellation
-   PDF une page par appellation
-   Reprise des données à partir des fichiers tableurs (à convertir en
    csv pour tester l'uniformité)
-   Ajout d'un tableau Vendeur / Apellations dans le récap
-   On ne met en valeur que les modifications ou les ajouts dans la visualisation

Planning
--------

-   10 Février - Parcellaire : Envoi des codes de création + liste
    déclarants (Pour envoi courrier)
-   16 Février - Parcellaire : Fine tuning, venu de Vicky chez Actualys
-   18 Février - Parcellaire : Mise en production
-   26 Février - Dégustation : Mise en production
-   12 Mars - Facturation, Dégustration : Réunion en Alsace
-   Fin Avril Facturation : Pouvoir envoyer les factures

Annexe
------

-   Mockups de le création d'une tournée (1 page)
-   Mockups de l'organisation d'une dégustation (4 pages)
-   Courrier actuel du résultat de dégustation (déstiné à l'opérateur) (3 pages)
-   Mockups du parcellaire (4 pages)