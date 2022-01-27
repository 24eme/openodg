#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
import sys
import os
import re
from datetime import datetime

path_igp = "../../web/exports_igp"

if(len(sys.argv) > 1 and re.search("^igp", sys.argv[1])):
     path_igp = "../../web/exports_"+sys.argv[1]

millesime = str(datetime.now().year - 1)

if(len(sys.argv) > 2 and re.search("^[0-9]{4}$", sys.argv[2])):
    millesime = sys.argv[2]
    
outputdir = path_igp+'/stats/'+millesime
if(not os.path.isdir(outputdir)):
    os.mkdir(outputdir)   
    
millesime_precedent = str(int(millesime) - 1)

moisjour = "12-31"
    
if(len(sys.argv) > 3 and re.search("^[0-9]{2}-[0-9]{2}$", sys.argv[3])):
    moisjour = sys.argv[3]
    
date_debut_courant = millesime + '-08-01'
date_fin_courant = str(int(millesime) + 1) + '-' + moisjour

date_debut_precedent = millesime_precedent + '-08-01'
date_fin_precedent = str(int(millesime_precedent) + 1) + '-' + moisjour


# In[ ]:


historique = pd.read_csv(path_igp+ "/lots-historique.csv",  encoding="iso8859_15", delimiter=";", decimal=",", 
                   dtype={'Campagne': 'str', 'Num dossier': 'str', 
                          'Num lot': 'str', 'Millésime': 'str'}, low_memory=False)

historique = historique[(historique['Origine'] == 'DRev') | (historique['Origine'] == 'DRev:Changé')]
historique = historique[((historique['Libellé du lot'].str.contains(" " + millesime + " ")) & (historique['Date lot'] >= date_debut_courant) & (historique['Date lot'] <= date_fin_courant)) | ((historique['Libellé du lot'].str.contains(" " + millesime_precedent + " ")) & (historique['Date lot'] >= date_debut_precedent) & (historique['Date lot'] <= date_fin_precedent))]

historique["unique_id"] = historique['Id Opérateur'] + historique['Lot unique Id']

historique_conforme_unique_id = historique[(historique['Statut'] == 'Conforme') | (historique['Statut'] == 'Réputé conforme') | (historique['Statut'] == 'Conforme en appel')]['unique_id'].unique()
historique_revendique_unique_id = historique['unique_id'].unique()

historique = historique.sort_values(by=['Date lot', 'Doc Ordre'], ascending=False)
historique = historique.drop_duplicates(subset=['Id Opérateur', 'Lot unique Id'], keep='first')

historique_revendique = historique[historique['unique_id'].isin(historique_revendique_unique_id)]
historique_conforme = historique[historique['unique_id'].isin(historique_conforme_unique_id)]


# In[ ]:


lots = pd.read_csv(path_igp+"/lots.csv", encoding="iso8859_15", delimiter=";", decimal=",",
                   dtype={'Code postal Opérateur': 'str', 'Campagne': 'str', 'Num dossier': 'str',
                          'Num lot': 'str', 'Millésime': 'str'}, low_memory=False)
lots['Lieu'].fillna('', inplace=True)
lots_revendique = lots.merge(historique_revendique, how='inner', on=['Id Opérateur', 'Lot unique Id'])
lots_conforme = lots.merge(historique_conforme, how='inner', on=['Id Opérateur', 'Lot unique Id'])


# In[ ]:


group = ['Produit', 'Appellation', 'Couleur', 'Lieu']
stat_igp = lots_revendique[(lots_revendique['Millésime'] == millesime)].groupby(group)[['Volume_y']].sum().rename(columns={"Volume_y": "VRT " + millesime})
stat_igp['VRT ' + millesime_precedent] = lots_revendique[(lots_revendique['Millésime'] == millesime_precedent)].groupby(group)[['Volume_y']].sum()
stat_igp['VRC ' + millesime] = lots_conforme[(lots_conforme['Millésime'] == millesime)].groupby(group)[['Volume_y']].sum()
stat_igp['VRC ' + millesime_precedent] = lots_conforme[(lots_conforme['Millésime'] == millesime_precedent)].groupby(group)[['Volume_y']].sum()


# In[ ]:


stat_igp.reset_index().to_csv(outputdir+"/igp_stats_vrc-vrt_"+millesime+"_"+date_fin_courant+".csv", encoding="iso8859_15", sep=";", index=False, decimal=",")

