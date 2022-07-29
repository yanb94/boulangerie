---
title: Contact
form_title: "Nous contacter"
metadata:
    description: "Contactez les équipes de la boulangerie le Bon Pain"
    "og:url": "[my_base_url]/contact"
    "og:title": "Contact | Le Bon Pain"
    "og:description": "Contactez les équipes de la boulangerie le Bon Pain"
    "og:image": "[my_base_url]/user/themes/boulangerie/images/contact.jpg"
    "og:type": website
    "og:site_name": "Le Bon Pain"
    "twitter:card": summary_large_image
    "twitter:site": "@lebonpain"
    "twitter:creator": "@lebonpain"
onpage_menu: false
form:
    name: contact
    action: /contact
    fields:
        name:
            label: Nom
            placeholder: "Entrer votre nom"
            autocomplete: "on"
            type: text
            validate:
                required: true
        email:
            label: "Adresse email"
            placeholder: "Entrer votre email"
            type: email
            validate:
                required: true
        message:
            label: Message
            placeholder: "Entrer votre message"
            type: textarea
            validate:
                required: true
    buttons:
        submit:
            type: submit
            value: Envoyer
    process:
        save:
            fileprefix: contact-
            dateformat: Ymd-His-u
            extension: html
            body: "{% include 'forms/data.html.twig' %}"
        email:
            subject: "[Site Contact Form] {{ form.value.name|e }}"
            body: "{% include 'forms/data.html.twig' %}"
        message: "Votre message a bien été pris encompte nous vous répondrons dans les meilleurs délais"
        redirect: /contact/thankyou
sitemap:
    priority: 0.6
---
