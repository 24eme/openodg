import sys, os, re, pandas as pd
from sqlalchemy import create_engine
engine = create_engine('sqlite:///'+sys.argv[1], echo=False, encoding='iso-8859-1')

numeric_cols = ['Volume', 'Superficie', 'VCI', 'Quantite', 'Prix', 'Montant', 'Surface', 'Ecart pieds', 'Ecart rang', 'Densite', 'Pourcentage']
numeric_cols_strict = ['Valeur', 'TVA', 'Lat', 'Lon']

read_chunksize = 1000


def convert_float_columns(csv):
    for col in csv.columns:
        result = [col for v in numeric_cols if v in col] or [col for v in numeric_cols_strict if col in v]
        if len(result):
            csv[col] = pd.to_numeric(csv[col].str.replace(',', '.'), errors='coerce')

    return csv


def save(reader, table):
    i = 0
    for chunk in reader:
        chunk = convert_float_columns(chunk)
        mode = 'replace' if i == 0 else 'append'
        chunk.to_sql(table, con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
        i += 1


if os.path.exists(sys.argv[2]+"/etablissements.csv") and os.path.getsize(sys.argv[2]+"/etablissements.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/etablissements.csv\n")
    reader = pd.read_csv(sys.argv[2] + "/etablissements.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'etablissement')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/chais.csv") and os.path.getsize(sys.argv[2]+"/chais.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/chais.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/chais.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'chai')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/societe.csv") and os.path.getsize(sys.argv[2]+"/societe.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/societe.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/societe.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'societe')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/habilitation.csv") and os.path.getsize(sys.argv[2]+"/habilitation.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/habilitation.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/habilitation.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'habilitation')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/habilitation_demandes.csv") and os.path.getsize(sys.argv[2]+"/habilitation_demandes.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/habilitation_demandes.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/habilitation_demandes.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'habilitation_demandes')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/drev.csv") and os.path.getsize(sys.argv[2]+"/drev.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/drev.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/drev.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'drev')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/dr.csv") and os.path.getsize(sys.argv[2]+"/dr.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/dr.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/dr.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    i = 0
    for chunk in reader:
      mode = 'replace' if i == 0 else 'append'
      i += 1
      chunk = convert_float_columns(chunk)
      chunk['CVI'] = chunk['CVI'].str.zfill(10)
      chunk['CVI Tiers'] = chunk['CVI Tiers'].str.zfill(10)
      chunk.to_sql('dr', con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/sv12.csv") and os.path.getsize(sys.argv[2]+"/sv12.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/sv12.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/sv12.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    i = 0
    for chunk in reader:
      mode = 'replace' if i == 0 else 'append'
      i += 1
      chunk = convert_float_columns(chunk)
      chunk['CVI'] = chunk['CVI'].str.zfill(10)
      chunk['CVI Tiers'] = chunk['CVI Tiers'].str.zfill(10)
      chunk.to_sql('sv12', con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/sv11.csv") and os.path.getsize(sys.argv[2]+"/sv11.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/sv11.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/sv11.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    i = 0
    for chunk in reader:
      mode = 'replace' if i == 0 else 'append'
      i += 1
      chunk = convert_float_columns(chunk)
      chunk['CVI'] = chunk['CVI'].str.zfill(10)
      chunk['CVI Tiers'] = chunk['CVI Tiers'].str.zfill(10)
      chunk.to_sql('sv11', con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/production.csv") and os.path.getsize(sys.argv[2]+"/production.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/production.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/production.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    i = 0
    for chunk in reader:
      mode = 'replace' if i == 0 else 'append'
      i += 1
      chunk = convert_float_columns(chunk)
      chunk['CVI'] = chunk['CVI'].str.zfill(10)
      chunk['CVI Tiers'] = chunk['CVI Tiers'].str.zfill(10)
      chunk.to_sql('production', con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/drev_marc.csv") and os.path.getsize(sys.argv[2]+"/drev_marc.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/drev_marc.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/drev_marc.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'drev_marc')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/facture.csv") and os.path.getsize(sys.argv[2]+"/facture.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/facture.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/facture.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'facture')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/parcellaire.csv") and os.path.getsize(sys.argv[2]+"/parcellaire.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/parcellaire.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/parcellaire.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'parcellaire')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/parcellaireirrigable.csv") and os.path.getsize(sys.argv[2]+"/parcellaireirrigable.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/parcellaireirrigable.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/parcellaireirrigable.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'parcellaireirrigable')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/parcellaireirrigue.csv") and os.path.getsize(sys.argv[2]+"/parcellaireirrigue.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/parcellaireirrigue.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/parcellaireirrigue.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'parcellaireirrigue')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/parcellaireintentionaffectation.csv") and os.path.getsize(sys.argv[2]+"/parcellaireintentionaffectation.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/parcellaireintentionaffectation.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/parcellaireintentionaffectation.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'parcellaireintentionaffectation')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/parcellaireaffectation.csv") and os.path.getsize(sys.argv[2]+"/parcellaireaffectation.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/parcellaireaffectation.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/parcellaireaffectation.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'parcellaireaffectation')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/parcellairemanquant.csv") and os.path.getsize(sys.argv[2]+"/parcellairemanquant.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/parcellairemanquant.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/parcellairemanquant.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'parcellairemanquant')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/travaux_marc.csv") and os.path.getsize(sys.argv[2]+"/travaux_marc.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/travaux_marc.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/travaux_marc.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'travaux_marc')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/tirage.csv") and os.path.getsize(sys.argv[2]+"/tirage.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/tirage.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/tirage.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'tirage')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/registre_vci.csv") and os.path.getsize(sys.argv[2]+"/registre_vci.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/registre_vci.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/registre_vci.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'registre_vci')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/constats.csv") and os.path.getsize(sys.argv[2]+"/constats.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/constats.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/constats.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'constats')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/facture_stats.csv") and os.path.getsize(sys.argv[2]+"/facture_stats.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/facture_stats.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/facture_stats.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'facture_stats')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/bilan_vci.csv") and os.path.getsize(sys.argv[2]+"/bilan_vci.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/bilan_vci.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/bilan_vci.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'bilan_vci')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/pieces.csv") and os.path.getsize(sys.argv[2]+"/pieces.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/pieces.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/pieces.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'pieces')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/liaisons.csv") and os.path.getsize(sys.argv[2]+"/liaisons.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/liaisons.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/liaisons.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'liaisons')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/comptes.csv") and os.path.getsize(sys.argv[2]+"/comptes.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/comptes.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/comptes.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'comptes')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/lots.csv") and os.path.getsize(sys.argv[2]+"/lots.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/lots.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/lots.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    i = 0
    for chunk in reader:
      mode = 'replace' if i == 0 else 'append'
      i += 1
      chunk = convert_float_columns(chunk)
      # CVI Opérateur
      chunk[chunk.columns[4]] = chunk[chunk.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      # Millésime
      chunk[chunk.columns[21]] = chunk[chunk.columns[21]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      chunk.to_sql('lots', con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/lots-historique.csv") and os.path.getsize(sys.argv[2]+"/lots-historique.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/lots-historique.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/lots-historique.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    i = 0
    for chunk in reader:
      mode = 'replace' if i == 0 else 'append'
      i += 1
      chunk = convert_float_columns(chunk)
      # Lot Code postal Opérateur
      chunk[chunk.columns[22]] = chunk[chunk.columns[22]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      # Lot Millésime
      chunk[chunk.columns[39]] = chunk[chunk.columns[39]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      chunk.to_sql('lots-historique', con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/lots_suivi.csv") and os.path.getsize(sys.argv[2]+"/lots_suivi.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/lots_suivi.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/lots_suivi.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    i = 0
    for chunk in reader:
      mode = 'replace' if i == 0 else 'append'
      i += 1
      chunk = convert_float_columns(chunk)
      #CVI Opérateur
      chunk[chunk.columns[4]] = chunk[chunk.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #Millésime
      chunk[chunk.columns[22]] = chunk[chunk.columns[22]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      chunk.to_sql('lots_suivi', con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/drev_lots.csv") and os.path.getsize(sys.argv[2]+"/drev_lots.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/drev_lots.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/drev_lots.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    i = 0
    for chunk in reader:
      mode = 'replace' if i == 0 else 'append'
      i += 1
      chunk = convert_float_columns(chunk)
      #CVI Opérateur
      chunk[chunk.columns[4]] = chunk[chunk.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #SIRET Opérateur
      chunk[chunk.columns[5]] = chunk[chunk.columns[5]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #Millésime
      chunk[chunk.columns[25]] = chunk[chunk.columns[25]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      chunk.to_sql('drev_lots', con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/conditionnement_lots.csv") and os.path.getsize(sys.argv[2]+"/conditionnement_lots.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/conditionnement_lots.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/conditionnement_lots.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    i = 0
    for chunk in reader:
      mode = 'replace' if i == 0 else 'append'
      i += 1
      chunk = convert_float_columns(chunk)
      #CVI Opérateur
      chunk[chunk.columns[4]] = chunk[chunk.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #SIRET Opérateur
      chunk[chunk.columns[5]] = chunk[chunk.columns[5]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #Millésime
      chunk[chunk.columns[25]] = chunk[chunk.columns[25]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      chunk.to_sql('conditionnement_lots', con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/transaction_lots.csv") and os.path.getsize(sys.argv[2]+"/transaction_lots.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/transaction_lots.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/transaction_lots.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    i = 0
    for chunk in reader:
      mode = 'replace' if i == 0 else 'append'
      i += 1
      chunk = convert_float_columns(chunk)
      #CVI Opérateur
      chunk[chunk.columns[4]] = chunk[chunk.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #SIRET Opérateur
      chunk[chunk.columns[5]] = chunk[chunk.columns[5]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #Millésime
      chunk[chunk.columns[25]] = chunk[chunk.columns[25]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      chunk.to_sql('transaction_lots', con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/pmc_lots.csv") and os.path.getsize(sys.argv[2]+"/transaction_lots.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/pmc_lots.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/pmc_lots.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    i = 0
    for chunk in reader:
      mode = 'replace' if i == 0 else 'append'
      i += 1
      chunk = convert_float_columns(chunk)
      #CVI Opérateur
      chunk[chunk.columns[4]] = chunk[chunk.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #SIRET Opérateur
      chunk[chunk.columns[5]] = chunk[chunk.columns[5]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #Millésime
      chunk[chunk.columns[25]] = chunk[chunk.columns[25]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      chunk.to_sql('pmc_lots', con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/pmcnc_lots.csv") and os.path.getsize(sys.argv[2]+"/transaction_lots.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/pmcnc_lots.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/pmcnc_lots.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    i = 0
    for chunk in reader:
      mode = 'replace' if i == 0 else 'append'
      i += 1
      chunk = convert_float_columns(chunk)
      #CVI Opérateur
      chunk[chunk.columns[4]] = chunk[chunk.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #SIRET Opérateur
      chunk[chunk.columns[5]] = chunk[chunk.columns[5]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #Millésime
      chunk[chunk.columns[25]] = chunk[chunk.columns[25]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      chunk.to_sql('pmcnc_lots', con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/changement_denomination.csv") and os.path.getsize(sys.argv[2]+"/changement_denomination.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/changement_denomination.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/changement_denomination.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    i = 0
    for chunk in reader:
      mode = 'replace' if i == 0 else 'append'
      i += 1
      chunk = convert_float_columns(chunk)
      #CVI Opérateur
      chunk[chunk.columns[4]] = chunk[chunk.columns[4]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #SIRET Opérateur
      chunk[chunk.columns[5]] = chunk[chunk.columns[5]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #Origine Millésime
      chunk[chunk.columns[26]] = chunk[chunk.columns[26]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      #Millésime
      chunk[chunk.columns[45]] = chunk[chunk.columns[45]].apply(lambda x: str(x).replace(".0", "").replace("nan", ""))
      chunk.to_sql('changement_denomination', con=engine, if_exists=mode, chunksize=read_chunksize, method="multi")
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/factures.csv") and os.path.getsize(sys.argv[2]+"/factures.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/factures.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/factures.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'factures')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/paiements.csv") and os.path.getsize(sys.argv[2]+"/paiements.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/paiements.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/paiements.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'paiements')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");

if os.path.exists(sys.argv[2]+"/degustations.csv") and os.path.getsize(sys.argv[2]+"/degustations.csv"):
  try:
    sys.stderr.write(sys.argv[2]+"/degustations.csv\n")
    reader = pd.read_csv(sys.argv[2]+"/degustations.csv", chunksize=read_chunksize, encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False, dtype = 'str')
    save(reader, 'degustations')
  except Exception as e:
    sys.stderr.write("ERROR: unable to read csv file:\n\t"+str(e)+"\n");
