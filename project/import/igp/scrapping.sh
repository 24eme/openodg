mkdir -p imports
if [ -d "imports/$(cat config.json | jq '.file_name' | sed s/\"//g)" ]; then
  rm -r "imports/$(cat config.json | jq '.file_name' | sed s/\"//g)";
fi

FILE_NAME=$(cat config.json | jq '.file_name' | sed s/\"//g)

mkdir -p imports/$(cat config.json | jq '.file_name' | sed s/\"//g)
if test "$DISPLAY"; then
  node scrapping.js
  node scrapping_cepages.js
  node scrapping_membres_innactifs.js

else
  xvfb-run -a --server-args="-screen 0 1366x768x24" node scrapping.js
  xvfb-run -a --server-args="-screen 0 1366x768x24" node scrapping_cepages.js
  xvfb-run -a --server-args="-screen 0 1366x768x24" node scrapping_membres_innactifs.js
fi

bash script_verify.sh
sed "s/\t/;/" imports/$FILE_NAME/produits.txt >> imports/$FILE_NAME/produits.csv
sed "s/\t/;/" imports/$FILE_NAME/cépages.txt | cut -d ";" -f 1 >> imports/$FILE_NAME/cépages.csv
sed "s/\t/;/" imports/$FILE_NAME/membres_innactifs.txt >> imports/$FILE_NAME/membres_innactifs.csv
sed "s/\t/;/g"  imports/var/membres_innactifs.txt >> imports/var/membres_innactifs.csv
