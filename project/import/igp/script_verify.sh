if [ -f "config.json" ];then
  if [ -d "imports/$(cat config.json | jq '.file_name' | sed s/\"//g)" ];then
  cd imports/$(cat config.json | jq '.file_name' | sed s/\"//g)
   if [ "$(ls -1 | wc -l)" -ne "28" ];then
     echo 'Il manque des fichiers'
   fi
   cd ..
  fi
fi
