ls . $(echo $0 | sed 's/[^\/]*$//')../ | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | while read app; do
    echo bash $(echo $0 | sed 's/[^\/]*$//')generation.sh $app
done