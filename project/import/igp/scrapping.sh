
if ! test "$1"; then
    echo "Fichier config requis";
    exit 1;
fi

CONFIGFILE=$1

mkdir -p imports
if [ -d "imports/$(cat $CONFIGFILE | jq '.file_name' | sed s/\"//g)" ]; then
  rm -r "imports/$(cat $CONFIGFILE | jq '.file_name' | sed s/\"//g)";
fi

FILE_NAME=$(cat $CONFIGFILE | jq '.file_name' | sed s/\"//g)

mkdir -p imports/$(cat $CONFIGFILE | jq '.file_name' | sed s/\"//g)
if test "$DISPLAY"; then
  node scrapping.js $CONFIGFILE
else
  xvfb-run -a --server-args="-screen 0 1366x768x24" node scrapping.js $CONFIGFILE
fi

bash script_verify.sh
