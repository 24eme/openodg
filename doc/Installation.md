Installation
============

Récupération du projet

 > git clone git@gitorious.org:ava/ava.git


Subtree et dépendance
=====================

### Ajouter une dépendance par subtree

 > git remote add <path/to/lib> <url_to_lib_remote> 
 > git subtree add --prefix=<path/to/lib> <path/to/lib> <branch> --squash

Exemple d'ajout du plugin couchdb :

 > git remote add project/plugins/acCouchdbPlugin git@gitorious.org:accouchdbplugin/accouchdbplugin.git
 > git subtree add --prefix=project/plugins/acCouchdbPlugin project/plugins/acCouchdbPlugin master --squash

### Récupérer les mise à jour d'une dépendance subtree

> git subtree pull --prefix=project/plugins/acVinConfigurationPlugin project/plugins/acVinConfigurationPlugin ava --squash

### Pousser les mises à jour effectuées

 > git subtree push --prefix=project/plugins/acVinConfigurationPlugin project/plugins/acVinConfigurationPlugin ava
