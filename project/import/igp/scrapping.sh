mkdir -p imports
if [ -d "imports/$(cat config.json | jq '.file_name' | sed s/\"//g)" ]; then
  rm -r "imports/$(cat config.json | jq '.file_name' | sed s/\"//g)";
fi


if ! test "$1"; then
    echo "nom du dossier";
    exit 1;
fi

mkdir -p imports/$1
node scrapping.js
node scrapping_cepages.js
bash script_verify.sh
