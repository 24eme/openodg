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
    drev = drev[drev['Campagne'] == campagne][['Campagne', 'Appellation','Appellation Libelle','Lieu','Lieu Libelle','Couleur', 'Produit', 'Superficie revendiquée', 'Volume revendiqué net total', 'Volume revendiqué issu du vci']]
    drev['Lieu Libelle'] = drev['Lieu Libelle'].fillna("DEFAUT")

    drev.loc[drev.Appellation != 'CVG','Lieu'] = 'DEFAUT'
    drev.loc[drev.Appellation != 'CVG','Lieu Libelle'] = 'DEFAUT'


    drev_groupby = drev.groupby(['Couleur','Appellation','Appellation Libelle','Lieu','Lieu Libelle']).sum()

    drev_total = drev.groupby(['Appellation','Appellation Libelle', 'Lieu','Lieu Libelle']).sum()
    drev_total['Couleur'] = 'total'


    drev_groupby = pd.concat([drev_groupby, drev_total.reset_index().set_index(['Couleur','Appellation','Appellation Libelle','Lieu','Lieu Libelle'])])

    drev_groupby['Rendement'] = drev_groupby['Volume revendiqué net total'] / drev_groupby['Superficie revendiquée']

    drev_appellation_lieu_couleur = drev_groupby[['Superficie revendiquée', 'Volume revendiqué net total', 'Volume revendiqué issu du vci', 'Rendement']].reset_index().pivot_table(columns=['Couleur'], index=['Appellation','Appellation Libelle','Lieu','Lieu Libelle']).fillna(0)
    #, values=['Superficie revendiquée', 'Volume revendiqué net total', 'Volume revendiqué issu du vci', 'Rendement']

    drev_appellation_lieu_couleur = drev_appellation_lieu_couleur.reset_index()

    #print(drev_appellation_lieu_couleur.columns.tolist())
    #columns = [('Appellation', ''), ('Appellation Libelle', ''), ('Lieu', ''), ('Lieu Libelle', ''), ('Rendement', 'blanc'), ('Rendement', 'rose'), ('Rendement', 'rouge'), ('Rendement', 'total'), ('Superficie revendiquée', 'blanc'), ('Superficie revendiquée', 'rose'), ('Superficie revendiquée', 'rouge'), ('Superficie revendiquée', 'total'), ('Volume revendiqué issu du vci', 'blanc'), ('Volume revendiqué issu du vci', 'rose'), ('Volume revendiqué issu du vci', 'rouge'), ('Volume revendiqué issu du vci', 'total'), ('Volume revendiqué net total', 'blanc'), ('Volume revendiqué net total', 'rose'), ('Volume revendiqué net total', 'rouge'), ('Volume revendiqué net total', 'total')]

    columns = [('Appellation', ''), ('Appellation Libelle', ''), ('Lieu', ''),('Lieu Libelle', ''),('Superficie revendiquée', 'rouge'), ('Superficie revendiquée', 'rose'), ('Superficie revendiquée', 'blanc'), ('Superficie revendiquée', 'total'), ('Volume revendiqué net total', 'rouge'), ('Volume revendiqué net total', 'rose'), ('Volume revendiqué net total', 'blanc'), ('Volume revendiqué net total', 'total'), ('Volume revendiqué issu du vci', 'rouge'), ('Volume revendiqué issu du vci', 'rose'), ('Volume revendiqué issu du vci', 'blanc'), ('Volume revendiqué issu du vci', 'total'), ('Rendement', 'rouge'), ('Rendement', 'rose'), ('Rendement', 'blanc'), ('Rendement', 'total')]

    drev_appellation_lieu_couleur = drev_appellation_lieu_couleur[columns]

    drev_appellation_lieu_couleur.to_csv("../../web/exports/stats/stats_drev_"+ campagne +".csv", encoding="iso8859_15", sep=";", index=False, decimal=",",header=['Appellation','Appellation Libelle','Lieu','Lieu Libelle','Superficie revendiquée rouge','Superficie revendiquée rose','Superficie revendiquée blanc','Superficie revendiquée total','Volume revendiqué net total rouge','Volume revendiqué net total rose','Volume revendiqué net total blanc','Volume revendiqué net total total','Volume revendiqué issu du vci rouge','Volume revendiqué issu du vci rose','Volume revendiqué issu du vci blanc','Volume revendiqué issu du vci total','Rendement rouge','Rendement rose','Rendement blanc','Rendement total'])



# In[ ]:


createCSVStatByCampagne(drev["Campagne"].unique()[-1],drev)
createCSVStatByCampagne(drev["Campagne"].unique()[-2],drev)

