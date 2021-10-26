#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
drev = pd.read_csv("../../web/exports/drev.csv", encoding="iso8859_15", delimiter=";", decimal=",",dtype={'CVI Opérateur': 'str', 'Identifiant': 'str','Appellation': 'str','Campagne': 'str'}, low_memory=False)

drev = drev[['Campagne','Produit','Nom Opérateur','Adresse Opérateur','Code postal Opérateur','Commune Opérateur','CVI Opérateur','Siret Opérateur','VCI Stock précédent','Superficie revendiqué','Volume revendiqué issu du mutage','Volume revendiqué issu du vci','VCI Complément','VCI Substitution','VCI Rafraichi','VCI Destruction','Volume revendiqué issu de la récolte','VCI Stock final']]


# In[ ]:


campagne = drev['Campagne'].unique()[-1]
drev_campagne = drev[drev['Campagne'] == campagne].fillna(0)
drev_campagne['has_stock'] = drev_campagne['VCI Stock précédent'] + drev_campagne['VCI Stock final']
drev_campagne['VCI N-1 revendiqué'] = drev_campagne['Volume revendiqué issu du vci'] + drev_campagne['VCI Destruction']
drev_campagne = drev_campagne[drev_campagne['has_stock'] > 0]
#drev_campagne = drev_campagne.sort_values(['Produit'])  
drev_campagne = drev_campagne.rename(columns={'VCI Stock précédent': 'VCI N-1','Superficie revendiqué':'Surface (L4)','Volume revendiqué issu du mutage': 'Vol AOC (L15)','Volume revendiqué issu du vci':'Vol VCI (L19)', 'VCI Complément': 'Complément','VCI Substitution':'Substitution','VCI Rafraichi':'Rafraichissement','VCI Destruction':'Destruction','Volume revendiqué issu de la récolte':'Vol AOC issu de la récolte'}) 
drev_campagne = drev_campagne.drop(['has_stock', 'Campagne','VCI Stock final'],axis=1) 
drev_campagne.loc['Total'] = drev_campagne[['Complément','VCI N-1','Rafraichissement','Destruction','VCI N-1 revendiqué']].sum()
drev_campagne.reset_index(drop=True).to_csv("../../web/exports/stats/inao_vci_suivi_"+campagne+".csv", encoding="iso8859_15", sep=";", index=False, decimal=",")

