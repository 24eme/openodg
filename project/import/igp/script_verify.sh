if ! test "$1"; then
    echo "Fichier config requis";
    exit 1;
fi

CONFIGFILE=$1

if [ -f "config.json" ];then
  if [ -d "imports/$(cat $CONFIGFILE | jq '.file_name' | sed s/\"//g)" ];then
  cd imports/$(cat $CONFIGFILE | jq '.file_name' | sed s/\"//g)
   if [ "$(ls -1 | wc -l)" -ne "31" ];then
     echo 'Il manque des fichiers'
   fi
  fi
fi
