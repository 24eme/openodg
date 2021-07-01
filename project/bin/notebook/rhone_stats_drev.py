#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
drev = pd.read_csv("../../web/exports/drev.csv", encoding="iso8859_15", delimiter=";", decimal=",", 
                   dtype={'CVI Opérateur': 'str', 'Siret Opérateur': 'str', 'Identifiant': 'str', 
                          'Produit (millesime)': 'str', 'Date Rev': 'str', 'Destination': 'str', 
                          'Campagne': 'str', 'Numéro du lot': 'str', 
                          'Code postal Opérateur': 'str'}, low_memory=False)
#drev.columns


# In[ ]:


drev_2020 = drev[drev['Campagne'] == '2020'][['Campagne', 'Appellation','Appellation Libelle','Lieu','Lieu Libelle','Couleur', 'Produit', 'Superficie revendiqué', 'Volume revendiqué net total', 'Volume revendiqué issu du vci']]
#drev_2020

drev_2020['Lieu Libelle'] = drev_2020['Lieu Libelle'].fillna("DEFAUT")


# In[ ]:


drev_2020_groupby = drev_2020.groupby(['Couleur','Appellation','Appellation Libelle','Lieu','Lieu Libelle']).sum()


# In[ ]:


#drev_2020_groupby = drev_2020.groupby(['Couleur','Appellation','Lieu']).sum()
drev_total = drev_2020.groupby(['Appellation','Appellation Libelle', 'Lieu','Lieu Libelle']).sum()
drev_total['Couleur'] = 'total'

drev_2020_groupby = pd.concat([drev_2020_groupby, drev_total.reset_index().set_index(['Couleur','Appellation','Appellation Libelle','Lieu','Lieu Libelle'])])
drev_2020_groupby['Rendement'] = drev_2020_groupby['Volume revendiqué net total'] / drev_2020_groupby['Superficie revendiqué']


# In[ ]:


drev_2020_appellation_lieu_couleur = drev_2020_groupby[['Superficie revendiqué', 'Volume revendiqué net total', 'Volume revendiqué issu du vci', 'Rendement']].reset_index().pivot(columns=['Couleur'], index=['Appellation','Appellation Libelle', 'Lieu','Lieu Libelle']).fillna(0)

#print(drev_2020_appellation_lieu_couleur.columns.tolist())
columns = [('Superficie revendiqué', 'rouge'), ('Superficie revendiqué', 'rose'), ('Superficie revendiqué', 'blanc'), ('Superficie revendiqué', 'total'), ('Volume revendiqué net total', 'rouge'), ('Volume revendiqué net total', 'rose'), ('Volume revendiqué net total', 'blanc'), ('Volume revendiqué net total', 'total'), ('Volume revendiqué issu du vci', 'rouge'), ('Volume revendiqué issu du vci', 'rose'), ('Volume revendiqué issu du vci', 'blanc'), ('Volume revendiqué issu du vci', 'total'), ('Rendement', 'rouge'), ('Rendement', 'rose'), ('Rendement', 'blanc'), ('Rendement', 'total')]
drev_2020_appellation_lieu_couleur = drev_2020_appellation_lieu_couleur[columns]


# In[ ]:


drev_2020_appellation_lieu_couleur.reset_index().to_csv("../../web/exports/stats_drev_2020.csv", encoding="iso8859_15", sep=";", index=False, decimal=",")


# In[ ]:





# In[ ]:




