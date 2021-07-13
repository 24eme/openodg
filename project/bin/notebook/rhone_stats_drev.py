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


def createCSVStatByCampagne(campagne,drev):
    drev = drev[drev['Campagne'] == campagne][['Campagne', 'Appellation','Appellation Libelle','Lieu','Lieu Libelle','Couleur', 'Produit', 'Superficie revendiqué', 'Volume revendiqué net total', 'Volume revendiqué issu du vci']]
    drev['Lieu Libelle'] = drev['Lieu Libelle'].fillna("DEFAUT")
    
    drev.loc[drev.Appellation != 'CVG','Lieu'] = 'DEFAUT' 
    drev.loc[drev.Appellation != 'CVG','Lieu Libelle'] = 'DEFAUT' 
    
    drev_groupby = drev.groupby(['Couleur','Appellation','Appellation Libelle','Lieu','Lieu Libelle']).sum()
    drev_total = drev.groupby(['Appellation','Appellation Libelle', 'Lieu','Lieu Libelle']).sum()
    drev_total['Couleur'] = 'total'

    drev_groupby = pd.concat([drev_groupby, drev_total.reset_index().set_index(['Couleur','Appellation','Appellation Libelle','Lieu','Lieu Libelle'])])
    drev_groupby['Rendement'] = drev_groupby['Volume revendiqué net total'] / drev_groupby['Superficie revendiqué']
    drev_appellation_lieu_couleur = drev_groupby[['Superficie revendiqué', 'Volume revendiqué net total', 'Volume revendiqué issu du vci', 'Rendement']].reset_index().pivot(columns=['Couleur'], index=['Appellation','Appellation Libelle', 'Lieu','Lieu Libelle']).fillna(0)

    #print(drev_2020_appellation_lieu_couleur.columns.tolist())
    columns = [('Superficie revendiqué', 'rouge'), ('Superficie revendiqué', 'rose'), ('Superficie revendiqué', 'blanc'), ('Superficie revendiqué', 'total'), ('Volume revendiqué net total', 'rouge'), ('Volume revendiqué net total', 'rose'), ('Volume revendiqué net total', 'blanc'), ('Volume revendiqué net total', 'total'), ('Volume revendiqué issu du vci', 'rouge'), ('Volume revendiqué issu du vci', 'rose'), ('Volume revendiqué issu du vci', 'blanc'), ('Volume revendiqué issu du vci', 'total'), ('Rendement', 'rouge'), ('Rendement', 'rose'), ('Rendement', 'blanc'), ('Rendement', 'total')]
    drev_appellation_lieu_couleur = drev_appellation_lieu_couleur[columns]  
    drev_appellation_lieu_couleur.reset_index().to_csv("../../web/exports/stats_drev_"+ campagne +".csv", encoding="iso8859_15", sep=";", index=False, decimal=",")


# In[ ]:


createCSVStatByCampagne(drev["Campagne"].unique()[-1],drev)
createCSVStatByCampagne(drev["Campagne"].unique()[-2],drev)

