mkdir -p imports
if [ -d "imports/$(cat config.json | jq '.file_name' | sed s/\"//g)" ]; then
  rm -r "imports/$(cat config.json | jq '.file_name' | sed s/\"//g)";
fi
node scrapping.js
bash script_verify.sh
