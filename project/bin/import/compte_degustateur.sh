
echo -n > /tmp/comptes_degustateurs_work.csv

cat /home/vince/www/ava/project/degustateurs.csv | awk -F ';' '{ print $1 ";1.COMPTE;;;;;" $2 ";;" $3 ";" $4 ";;" $5 ";" $6 ";;" $7 ";" $8 ";;;FRANCE;;;;;;;;;;;;"  }' >> /tmp/comptes_degustateurs_work.csv

cat /home/vince/www/ava/project/degustateurs.csv | awk -F ';' '{ print $1 ";5.COMMUN;;;;;;;;;multi;;;;;;;;;" $9 ";;;" $10 ";;;;;;"  }' >> /tmp/comptes_degustateurs_work.csv

cat /home/vince/www/ava/project/degustateurs.csv | cut -d ";" -f 1,11 | grep "PM" | cut -d ";" -f 1 | sed 's/$/;4.ATTRIB;;;;;;;;;;;;;;;;;;;;;;;Porteurs de mémoires;;;;/' >> /tmp/comptes_degustateurs_work.csv

cat /home/vince/www/ava/project/degustateurs.csv | cut -d ";" -f 1,11 | grep "T" | cut -d ";" -f 1 | sed 's/$/;4.ATTRIB;;;;;;;;;;;;;;;;;;;;;;;Techniciens du produit;;;;/' >> /tmp/comptes_degustateurs_work.csv

cat /home/vince/www/ava/project/degustateurs.csv | cut -d ";" -f 1,11 | grep "UP" | cut -d ";" -f 1 | sed 's/$/;4.ATTRIB;;;;;;;;;;;;;;;;;;;;;;;Usagers du produit;;;;/' >> /tmp/comptes_degustateurs_work.csv

cat /home/vince/www/ava/project/degustateurs.csv | cut -d ";" -f 1 | sed 's/$/;4.ATTRIB;;;;;;;;;;;;;;;;;;;;;;;Degustateur;;;;/' >> /tmp/comptes_degustateurs_work.csv

cat /tmp/comptes_degustateurs_work.csv | sort | sed -E 's/^([0-9]{5});/0\1;/' | sed -E 's/^([0-9]{4});/00\1;/' | sed -E 's/^([0-9]{3});/000\1;/' | sed -E 's/^([0-9]{2});/0000\1;/' | grep -E '^[0-9]+;' | sort | sed 's/;Monsieur;/;M;/' | sed 's/;Madame;/;MME;/' | sed 's/;Mme;/;MME;/' | sed 's/;Mademoiselle;/;MLLE;/' | sed 's/;Mlle;/;MLLE;/' | sed 's/;Melle;/;MLLE;/' | sed 's/;M ;/;M;/' | sed 's/;M\.;/;M;/'