#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
drev = pd.read_csv("../../web/exports/drev.csv", encoding="iso8859_15", delimiter=";", decimal=",", 
                   dtype={'CVI Opérateur': 'str', 'Siret Opérateur': 'str', 'Identifiant': 'str', 
                          'Produit (millesime)': 'str', 'Date Rev': 'str', 'Destination': 'str', 
                          'Campagne': 'str', 'Numéro du lot': 'str', 
                          'Code postal Opérateur': 'str'}, low_memory=False)


# In[ ]:


campagne = drev['Campagne'].unique()[-1]

drev_campagne_with_vci = drev[drev['Campagne'] == campagne].fillna(0)
drev_campagne_with_vci['has_stock'] = drev_campagne_with_vci['VCI Stock précédent'] + drev_campagne_with_vci['VCI Stock final']
drev_with_vci = drev_campagne_with_vci[drev_campagne_with_vci['has_stock'] > 0]
vci = drev_campagne_with_vci[['Produit', 'VCI Stock précédent', 'VCI Destruction', 'VCI Complément', 'VCI Substitution', 'VCI Rafraichi', 'VCI Constitué', 'VCI Stock final']].fillna(0)
stats_vci = vci.groupby(['Produit']).sum()
#stats_vci


# In[ ]:


stats_vci.reset_index().to_csv("../../web/exports/stats/inao_vci_"+campagne+".csv", encoding="iso8859_15", sep=";", index=False, decimal=",")


# In[ ]:


stats_drev_vci = drev_campagne_with_vci[['Produit', 'Superficie revendiquée', 'Volume revendiqué net total', 'Volume revendiqué issu de la récolte']].groupby('Produit').sum()
stats_drev_vci['nb déclarants'] = drev_campagne_with_vci[['Produit', 'CVI Opérateur']].groupby('Produit').count()
drev_with_vci_complete = drev_campagne_with_vci[['Produit', 'CVI Opérateur', 'VCI Complément']][drev_campagne_with_vci['VCI Complément'] > 0]
stats_drev_vci['VCI Complément - nb déclarants'] = drev_with_vci_complete[['Produit', 'CVI Opérateur']].groupby('Produit').count()
stats_drev_vci['VCI Complément - nb déclarants'] = stats_drev_vci['VCI Complément - nb déclarants'].fillna(0).astype(int)
stats_drev_vci['VCI Complément - hl'] = drev_with_vci_complete[['Produit', 'VCI Complément']].groupby('Produit').sum()
drev_with_vci_substitution = drev_campagne_with_vci[['Produit', 'CVI Opérateur', 'VCI Substitution']][drev_campagne_with_vci['VCI Substitution'] > 0]
stats_drev_vci['VCI Substitution - nb déclarants'] = drev_with_vci_substitution[['Produit', 'CVI Opérateur']].groupby('Produit').count()
stats_drev_vci['VCI Substitution - nb déclarants'] = stats_drev_vci['VCI Substitution - nb déclarants'].fillna(0).astype(int)
stats_drev_vci['VCI Substitution - hl'] = drev_with_vci_substitution[['Produit', 'VCI Substitution']].groupby('Produit').sum()
drev_with_vci_rafraichissement = drev_campagne_with_vci[['Produit', 'CVI Opérateur', 'VCI Rafraichi']][drev_campagne_with_vci['VCI Rafraichi'] > 0]
stats_drev_vci['VCI Rafraichissement - nb déclarants'] = drev_with_vci_rafraichissement[['Produit', 'CVI Opérateur']].groupby('Produit').count()
stats_drev_vci['VCI Rafraichissement - nb déclarants'] = stats_drev_vci['VCI Rafraichissement - nb déclarants'].fillna(0).astype(int)
stats_drev_vci['VCI Rafraichissement - hl'] = drev_with_vci_rafraichissement[['Produit', 'VCI Rafraichi']].groupby('Produit').sum()
drev_with_vci_detruit = drev_campagne_with_vci[['Produit', 'CVI Opérateur', 'VCI Destruction']][drev_campagne_with_vci['VCI Destruction'] > 0]
stats_drev_vci['VCI Détruit - nb déclarants'] = drev_with_vci_detruit[['Produit', 'CVI Opérateur']].groupby('Produit').count()
stats_drev_vci['VCI Détruit - nb déclarants'] = stats_drev_vci['VCI Détruit - nb déclarants'].fillna(0).astype(int)
stats_drev_vci['VCI Détruit - hl'] = drev_with_vci_detruit[['Produit', 'VCI Destruction']].groupby('Produit').sum()
drev_with_vci_stock_campagne_moins_un = drev_campagne_with_vci[['Produit', 'CVI Opérateur', 'VCI Stock précédent']][drev_campagne_with_vci['VCI Destruction'] > 0]
stats_drev_vci['VCI_campagne_moins_un - nb déclarants'] = drev_with_vci_stock_campagne_moins_un[['Produit', 'CVI Opérateur']].groupby('Produit').count()
stats_drev_vci['VCI_campagne_moins_un - nb déclarants'] = stats_drev_vci['VCI_campagne_moins_un - nb déclarants'].fillna(0).astype(int)
stats_drev_vci['VCI_campagne_moins_un - hl'] = drev_with_vci_stock_campagne_moins_un[['Produit', 'VCI Stock précédent']].groupby('Produit').sum()
stats_drev_vci = stats_drev_vci.fillna(0)


# In[ ]:


stats_drev_vci.reset_index().to_csv("../../web/exports/stats/inao_vci_drev_"+campagne+".csv", encoding="iso8859_15", sep=";", index=False, decimal=",")


# In[ ]:


dr_campagne = pd.read_csv("../../web/exports/dr.csv", encoding="iso8859_15", delimiter=";", decimal=",",
    dtype={'CVI': 'str', 'CVI Tiers': 'str', 'Identifiant': 'str', 'Code': 'str', 'Campagne': 'str'}, low_memory=False) 
dr_campagne = dr_campagne[(dr_campagne['Campagne'] == campagne) & ((dr_campagne['Appellation'] == 'CVG') | (dr_campagne['Appellation'] == 'CDR'))]
dr_campagne['Produit'] = dr_campagne['Appellation'] + dr_campagne['Couleur'] + dr_campagne['Lieu']


# In[ ]:


stats_dr_vci = dr_campagne[dr_campagne['Code'] == '04'][['Produit', 'Valeur']].groupby('Produit').sum().rename(columns={'Valeur': 'Superficie en production - L4'})
stats_dr_vci['Volume total produit - L5'] = dr_campagne[dr_campagne['Code'] == '05'][['Produit', 'Valeur']].groupby('Produit').sum()
stats_dr_vci['Rdt moyen - L5/L4'] = stats_dr_vci['Volume total produit - L5'] / stats_dr_vci['Superficie en production - L4']
stats_dr_vci['nb DR'] = dr_campagne[dr_campagne['Code'] == '04'][['Produit', 'CVI']].drop_duplicates().groupby('Produit').count()
#stats_drev_vci

cvi_with_vci = dr_campagne[(dr_campagne['Code'] == '19') & (dr_campagne['Valeur'] > 0)]['CVI']
dr_campagne_with_vci = dr_campagne[dr_campagne['CVI'].isin(cvi_with_vci)]

stats_dr_vci['vci - nb DR'] = dr_campagne_with_vci[dr_campagne_with_vci['Code'] == '19'][['Produit', 'CVI']].drop_duplicates().groupby('Produit').count()
stats_dr_vci = stats_dr_vci[stats_dr_vci['vci - nb DR'] > 0 ]

stats_dr_vci['vci - % DR'] = stats_dr_vci['vci - nb DR'] * 100 / stats_dr_vci['nb DR']


# In[ ]:


#stats_dr_vci['vci - superficie'] = dr_campagne_with_vci[dr_campagne_with_vci['Code'] == '04'][['Produit','CVI','Valeur']].sort_values('Valeur', ascending=False).drop_duplicates(subset=['Produit', 'CVI'], keep='first').groupby('Produit').sum()
stats_dr_vci['vci - superficie'] = dr_campagne_with_vci[dr_campagne_with_vci['Code'] == '04'][['Produit','Valeur']].groupby('Produit').sum()
stats_dr_vci['vci - % superficie'] = stats_dr_vci['vci - superficie'] * 100 / stats_dr_vci['Superficie en production - L4']


# In[ ]:


stats_dr_vci['vci - hl créé'] = dr_campagne_with_vci[dr_campagne_with_vci['Code'] == '19'][['Produit', 'Valeur']].groupby('Produit').sum()
stats_dr_vci['vci - hl moyen par DR'] = stats_dr_vci['vci - hl créé'] / stats_dr_vci['vci - nb DR']
stats_dr_vci['vci - rdmt'] = stats_dr_vci['vci - hl créé'] / stats_dr_vci['vci - superficie']

#merge avec les produits pour avoir les rendement et le libelle 
produits = pd.read_csv("../../web/exports/produits.csv", encoding="iso8859_15", delimiter=";", decimal=",", 
                   dtype={'appellation': 'str'}, low_memory=False, index_col = False)

produits['nom'] = produits['appellation'] + produits['couleur'].str.lower() + produits['lieu']

stats_dr_vci = pd.merge(stats_dr_vci,produits, how='left',left_on='Produit',right_on='nom')

stats_dr_vci['Produit'] = stats_dr_vci['nom']

stats_dr_vci['VCI déclaré / VCI autorisé'] = stats_dr_vci['vci - rdmt']* 100/ stats_dr_vci['Rend VCI total']

stats_dr_vci = stats_dr_vci[['Produit','libelle','Superficie en production - L4', 'Volume total produit - L5',
       'Rdt moyen - L5/L4', 'rend','Rend VCI total','nb DR', 'vci - nb DR', 'vci - % DR',
       'vci - superficie', 'vci - % superficie', 'vci - hl créé',
       'vci - hl moyen par DR', 'vci - rdmt','VCI déclaré / VCI autorisé']]


# In[ ]:


stats_dr_vci.reset_index(drop=True).to_csv("../../web/exports/stats/inao_vci_dr_"+campagne+".csv", encoding="iso8859_15", sep=";", index=False, decimal=",")

