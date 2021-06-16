#!/usr/bin/env python
# coding: utf-8

# In[8]:


import pandas as pd

lots = pd.read_csv("../../web/exports_igp/lots.csv", encoding="iso8859_15", delimiter=";", decimal=",", 
                   dtype={'Code postal Opérateur': 'str', 'Campagne': 'str', 'Num dossier': 'str', 
                          'Num lot': 'str', 'Millésime': 'str'}, low_memory=False)
lots.columns


# In[52]:


lots_2020 = lots[(lots['Millésime'] == '2020') & (lots['Date lot'] >= '2020-08-01') & (lots['Date lot'] <= '2021-06-15')]
lots_2019 = lots[(lots['Millésime'] == '2019') & (lots['Date lot'] >= '2019-08-01') & (lots['Date lot'] <= '2020-06-15')]

stat_igp = lots_2020.groupby('Produit')[['Volume']].sum().rename(columns={"Volume": "VRT 2020"})
stat_igp['VRT 2019'] = lots_2019.groupby('Produit')[['Volume']].sum()

stat_igp


# In[45]:


historique = pd.read_csv("../../web/exports_igp/lots-historique.csv", encoding="iso8859_15", delimiter=";", decimal=",", 
                   dtype={'Code postal Opérateur': 'str', 'Campagne': 'str', 'Num dossier': 'str', 
                          'Num lot': 'str', 'Millésime': 'str'}, low_memory=False)
lots_controles = historique[(historique['Statut'] == 'Réputé conforme') | (historique['Statut'] == 'Conforme')]
lots_controles.columns


# In[53]:


lots_controles_2020 = lots_controles[(lots_controles['Campagne'] == '2020-2021') & (lots_controles['Date lot'] >= '2020-08-01') & (lots_controles['Date lot'] <= '2021-06-15')]
lots_controles_2019 = lots_controles[(lots_controles['Campagne'] == '2019-2020') & (lots_controles['Date lot'] >= '2019-08-01') & (lots_controles['Date lot'] <= '2020-06-15')]

lots_controles_2020.groupby('Produit')[['Volume']].sum()

stat_igp['VRC 2020'] = lots_controles_2020.groupby('Produit')[['Volume']].sum()
#stat_igp['VRC 2019'] = lots_controles_2019.groupby('Produit')[['Volume']].sum()
#stat_igp

