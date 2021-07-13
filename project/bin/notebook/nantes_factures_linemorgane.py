#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
factures = pd.read_csv("../../web/exports/factures.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Identifiant Analytique': 'str'}, low_memory=False)
factures = factures.fillna('')
factures['identifiant_ligne'] = factures.index
factures = factures[factures['Piece'] > 2000001]

factures_meta = factures[['identifiant_ligne', 'Date', 'Raison sociale', 'Adresse', 'Code Postal', 'Ville', 'Telephone fixe', 'Telephone Portable', 'eMail', 'Piece', 'id facture']]
factures_id = factures[['identifiant_ligne', 'id facture']]
factures_lignes = factures[['identifiant_ligne', 'Identifiant Analytique','Nom Cotisation', 'Cotisation Prix unitaire', 'Quantite Cotisation', 'Prix HT', 'TVA', 'Prix TTC']]


# In[ ]:


factures_pivot = factures_lignes.pivot(columns=['Nom Cotisation'], values=['Cotisation Prix unitaire', 'Quantite Cotisation', 'Prix HT', 'TVA', 'Prix TTC']).fillna(0)
factures_pivot = factures_id.join(factures_pivot, on=['identifiant_ligne']).groupby('id facture').sum()


# In[ ]:


factures_meta = factures_meta.groupby('id facture').first()


# In[ ]:


factures_meta['Cotisation valorisation HT']  = factures_pivot[('Prix HT', 'Cotisation valorisation - Superficie')]
factures_meta['Cotisation valorisation HT'] += factures_pivot[('Prix HT', 'Cotisation valorisation - VCI')]
factures_meta['Cotisation valorisation HT'] += factures_pivot[('Prix HT', 'Cotisation valorisation - Volume')]
factures_meta['Cotisation valorisation HT'] += factures_pivot[('Prix HT', 'Cotisation valorisation - Volume Crus')]

factures_meta['Cotisation valorisation TVA']  = factures_pivot[('TVA', 'Cotisation valorisation - Superficie')]
factures_meta['Cotisation valorisation TVA'] += factures_pivot[('TVA', 'Cotisation valorisation - VCI')]
factures_meta['Cotisation valorisation TVA'] += factures_pivot[('TVA', 'Cotisation valorisation - Volume')]
factures_meta['Cotisation valorisation TVA'] += factures_pivot[('TVA', 'Cotisation valorisation - Volume Crus')]

factures_meta['Remboursement valorisation covid HT']  = factures_pivot[('Prix HT', 'Cotisation valorisation - Remise exceptionnelle Covid')]

factures_meta['Remboursement valorisation covid TVA']  = factures_pivot[('TVA', 'Cotisation valorisation - Remise exceptionnelle Covid')]

factures_meta['Total valoriation TVA'] = factures_meta['Cotisation valorisation TVA'] + factures_meta['Remboursement valorisation covid TVA']
factures_meta['Total valoriation HT'] = factures_meta['Cotisation valorisation HT'] + factures_meta['Remboursement valorisation covid HT']
factures_meta['Total valoriation TTC'] = factures_meta['Total valoriation TVA'] + factures_meta['Total valoriation HT']

factures_meta['Cotisation ODG TOTAL ou forfait']  = factures_pivot[('Prix HT', 'Cotisation ODG - Superficie')]
factures_meta['Cotisation ODG TOTAL ou forfait'] += factures_pivot[('Prix HT', 'Cotisation ODG - VCI')]
factures_meta['Cotisation ODG TOTAL ou forfait'] += factures_pivot[('Prix HT', 'Cotisation ODG - Volume')]
factures_meta['Cotisation ODG TOTAL ou forfait'] += factures_pivot[('Prix HT', 'Cotisation ODG Forfait')]

factures_meta['Remboursement ODG covid']  = factures_pivot[('Prix HT', 'Cotisation ODG - Remise exceptionnelle Covid')]

factures_meta['ODG TOTAL + remise'] = factures_meta['Cotisation ODG TOTAL ou forfait'] + factures_meta['Remboursement ODG covid']

factures_meta['Droits I.N.A.O.'] = factures_pivot[('Prix HT', 'Droits I.N.A.O. (Art. 34, Loi 88/11/93 du 29/12/1988)')]

factures_meta['TOTAL ODG + INAO'] = factures_meta['ODG TOTAL + remise'] + factures_meta['Droits I.N.A.O.']
factures_meta['Total facture TTC'] = factures_pivot[('Prix TTC', 'Total facture')]


# In[ ]:


factures_meta[['Date', 'Raison sociale', 'Adresse', 'Code Postal', 'Ville', 'Telephone fixe', 'Telephone Portable', 'eMail', 'Piece',
               'Cotisation valorisation HT', 'Cotisation valorisation TVA',
               'Remboursement valorisation covid HT', 'Remboursement valorisation covid TVA', 
               'Total valoriation TVA', 'Total valoriation HT', 'Total valoriation TTC',
               'Cotisation ODG TOTAL ou forfait', 'Remboursement ODG covid', 'ODG TOTAL + remise',
               'Droits I.N.A.O.',
               'TOTAL ODG + INAO', 'Total facture TTC',]].to_csv('../../web/exports/factures_linemorgane.csv', encoding="iso8859_15", sep=";", decimal=",")


# In[ ]:




