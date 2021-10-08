#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
import sys
import re
from datetime import datetime

millesime = str(datetime.now().year - 1)

if(re.search("^[0-9]{4}#", sys.argv[1])):
    millesime = sys.argv[1]
    
millesime_precedent = str(int(millesime) - 1)

moisjour = "07-31"
    
if(re.search("^[0-9]{2}-[0-9]{2}#", sys.argv[2])):
    moisjour = sys.argv[2]
    
date_debut_courant = millesime + '-08-01'
date_fin_courant = str(int(millesime) + 1) + '-' + moisjour

date_debut_precedent = millesime_precedent + '-08-01'
date_fin_precedent = str(int(millesime_precedent) + 1) + '-' + moisjour


# In[ ]:


lots = pd.read_csv("../../web/exports_igp/lots.csv", encoding="iso8859_15", delimiter=";", decimal=",",
                   dtype={'Code postal Opérateur': 'str', 'Campagne': 'str', 'Num dossier': 'str',
                          'Num lot': 'str', 'Millésime': 'str'}, low_memory=False)
lots['Lieu'].fillna('', inplace=True)


# In[ ]:


lots = lots[(lots['Origine'] == 'DRev') | (lots['Origine'] == 'DRev:Changé')]


# In[ ]:


lots_courant = lots[(lots['Millésime'] == millesime) & (lots['Date lot'] >= date_debut_courant) & (lots['Date lot'] <= date_fin_courant)]
lots_precedent = lots[(lots['Millésime'] == millesime_precedent) & (lots['Date lot'] >= date_debut_precedent) & (lots['Date lot'] <= date_fin_precedent)]


# In[ ]:


group = ['Produit', 'Appellation', 'Couleur', 'Lieu']

stat_igp = lots_courant.groupby(group)[['Volume']].sum().rename(columns={"Volume": "VRT " + millesime})
stat_igp['VRT ' + millesime_precedent] = lots_precedent.groupby(group)[['Volume']].sum()


# In[ ]:


lots_conformes = lots[(lots['Statut de lot'] == 'Conforme') | (lots['Statut de lot'] == 'Réputé conforme') | (lots['Statut de lot'] == 'Conforme en appel')]
lots_conformes_courant = lots_conformes[(lots_conformes['Millésime'] == millesime) & (lots_conformes['Date lot'] >= date_debut_courant) & (lots_conformes['Date lot'] <= date_fin_courant)]
lots_conformes_precedent = lots_conformes[(lots_conformes['Millésime'] == millesime_precedent) & (lots_conformes['Date lot'] >= date_debut_precedent) & (lots_conformes['Date lot'] <= date_fin_precedent)]

stat_igp['VRC ' + millesime] = lots_conformes_courant.groupby(group)[['Volume']].sum()
stat_igp['VRC ' + millesime_precedent] = lots_conformes_precedent.groupby(group)[['Volume']].sum()

stat_igp = stat_igp.round({'VRC ' + millesime: 2, 'VRC ' + millesime_precedent: 2,'VRT ' + millesime: 2, 'VRT ' + millesime_precedent: 2})


# In[ ]:


stat_igp.reset_index().to_csv("../../web/exports_igp/igp_stats_vrc-vrt_2020.csv", encoding="iso8859_15", sep=";", index=False, decimal=",")

