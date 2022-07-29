#!/bin/bash

filename='.env'
key='STRIPE_PUBLIC_KEY'
output="user/plugins/panier/assets/js/env.js"

if test -f "/var/www/html/.env"
then
    while read line; do
        if [[ $line == *"$key"* ]]
        then
            stripe_key=$( echo -n "\"${line//$'\r'/}\"" | sed 's/STRIPE_PUBLIC_KEY=//' )
        fi
    done < $filename

    if test -f $output
    then
        rm $output
    fi

    touch $output

    echo "const ENV = {" >> $output
    echo "    STRIPE_PUBLIC_KEY:" >> $output
    echo "        $stripe_key" >> $output
    echo "};" >> $output

    echo "Initialisation des variables d'environnement pour javascript réalisé avec succès."

else 
    echo "Vous devez créer un fichier .env dans le dossier web du projet"
fi