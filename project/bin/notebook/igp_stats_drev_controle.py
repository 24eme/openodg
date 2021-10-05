#!/usr/bin/env python
# coding: utf-8

# In[1]:


import pandas as pd

lots = pd.read_csv("../../web/exports_igp/lots.csv", encoding="iso8859_15", delimiter=";", decimal=",", 
                   dtype={'Code postal Opérateur': 'str', 'Campagne': 'str', 'Num dossier': 'str', 
                          'Num lot': 'str', 'Millésime': 'str'}, low_memory=False)
lots['Lieu'].fillna('', inplace=True)
#lots.columns


# In[ ]:


historique = pd.read_csv("../../web/exports_igp/lots-historique.csv", encoding="iso8859_15", delimiter=";", decimal=",", 
                   dtype={'Campagne': 'str', 'Num dossier': 'str', 
                          'Num lot': 'str', 'Millésime': 'str'}, low_memory=False)
#historique['Doc Type'].unique()


# In[ ]:


uniq_id = historique[ 
    (historique['Doc Type'] != 'Conditionnement') & 
    (historique['Doc Type'] != 'Transaction') & 
    (historique['Campagne'] >= '2019-2020')
]['Lot unique Id'].unique()
lots = lots[lots['Lot unique Id'].isin(uniq_id)]
#lots = lots[lots['Appellation'] == 'MED']


# In[ ]:


lots_2020 = lots[(lots['Millésime'] == '2020') & (lots['Date lot'] >= '2020-08-01') & (lots['Date lot'] <= '2021-06-15')]
lots_2019 = lots[(lots['Millésime'] == '2019') & (lots['Date lot'] >= '2019-08-01') & (lots['Date lot'] <= '2020-06-15')]

#group = ['Produit']
group = ['Produit', 'Appellation', 'Couleur', 'Lieu']

stat_igp = lots_2020.groupby(group)[['Volume']].sum().rename(columns={"Volume": "VRT 2020"})
stat_igp['VRT 2019'] = lots_2019.groupby(group)[['Volume']].sum()

#stat_igp


# In[ ]:


lots_conformes = lots[(lots['Statut de lot'] == 'Conforme') | (lots['Statut de lot'] == 'Réputé conforme')]
lots_conformes_2020 = lots_conformes[(lots_conformes['Millésime'] == '2020') & (lots_conformes['Date lot'] >= '2020-08-01') & (lots_conformes['Date lot'] <= '2021-06-15')]
lots_conformes_2019 = lots_conformes[(lots_conformes['Millésime'] == '2019') & (lots_conformes['Date lot'] >= '2019-08-01') & (lots_conformes['Date lot'] <= '2020-06-15')]

stat_igp['VRC 2020'] = lots_conformes_2020.groupby(group)[['Volume']].sum()
stat_igp['VRC 2019'] = lots_conformes_2019.groupby(group)[['Volume']].sum()

#stat_igp


# In[ ]:


stat_igp.reset_index().to_csv("../../web/exports_igp/igp_stats_vrc-vrt_2020.csv", encoding="iso8859_15", sep=";", index=False, decimal=",")

