if [ -f "config.json" ];then
  if [ -d "imports/$(cat config.json | jq '.file_name' | sed s/\"//g)" ];then
  cd imports/$(cat config.json | jq '.file_name' | sed s/\"//g)
   if [ "$(ls -1 | wc -l)" -ne "31" ];then
     echo 'Il manque des fichiers'
   fi
  fi
fi
