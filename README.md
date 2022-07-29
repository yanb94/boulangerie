# Boulangerie (portfolio)

This repository contains a website for a bakery that is part of my portfolio.
The website is build with the cms Grav in its version 1.6.

## Limitations

The docker environnement who contains the website can only work on localhost with ssl certificate.
If you want to use another domain you need to change the virtual host in the apache folder and generate a new ssl certificate corresponding to your new domain by your own way.

I'm a french freelancer who target mainly client in france so the website will be only in french.

## Requirements

You need to have Docker with compose and a stripe account with sandbox keys.

## Installation

### Clone the project

Clone the project in the directory you want to execute it.

```sh
cd /path/to/myfolder
git clone https://github.com/yanb94/boulangerie.git
```

### Create an environnement file for the docker environnement

Create an environnent file for docker who contains the password for the database and the possibility to enable or not opcache revalidation of file base on timestamp.

```env
#.env
DB_PASSWORD=<your_database_password>
OPCACHE_REVALIDATE=1 #Recommended to leave on 1 for the first utilisation or if you plane to change the content
```

### Create an environnement file for the website

Create an environnement file for the website who contains:

-   Stripe public and private keys
-   Domain of the website (Default: https://localhost)
-   Pagination who correspond of the number of command you want to see one page on backoffice
-   Admin email

```env
#web/.env
STRIPE_PUBLIC_KEY=<your_stripe_public_key>
STRIPE_PRIVATE_KEY=<your_stripe_private_key>
DOMAIN=<domain_of_website>
PAGINATION_ORDER=<number_of_order_pagination>
ADMIN_EMAIL=<admin_email>
```

### Start docker container

```sh
cd /path/to/myfolder
docker-compose up
```

### Init the stripe public key to javascript

For the payment on the website work you need to pass the stripe public key to javascript.
For this you need to execute the following commands in the php-fpm container.

```sh
docker-compose exec php-fpm bash
bash initStripeToJs.sh
```

### Install the dependencies

For install the grav dependencies run the following command in the php-fpm container

```sh
php bin/grav install
```

### Create an admin account

Now if you go on the website url you should see a form where you can type your informations to create an admin account.
So, create your admin account.

### Configure the connection with the database

You should now configure the connection with the database.

-   Go on _<your_host>/admin_.
-   Log in with your account.
-   Go on _<your_host>/admin/config/database_
-   Type the following informations:
    ```
    "mysql" for host
    "boulangerie" as database
    "root" as username
    "<your_database_password>" as password
    ```
-   Save the informations

Possible issue in this step can happen see the section troubleshooting below.

### Configure your email informations

You should now configure your email informations.

-   Go on _<your_host>/admin/plugins/email_
-   Type your email informations according to your email account.

More informations about the email configuration in the [grav email plugin repository](https://github.com/getgrav/grav-plugin-email).

## Usage

### Buy products

#### From client

-   Go on the page where products are listed by clicking on "Nos produits" in the navbar
-   Scroll down and select the products you want to order by clicking on the cart button next to the price
-   Show the content of the cart by clicking on the cart icon in the navbar
-   Adjust the quantity and/or remove products as you wish and validate your cart by clicking on "Valider"
-   You are now on a page where you see the final content of your cart if your are satisfy of it click on "Valider" to continue
-   Now you are on the payement page when you can see the content of your cart and a stripe module to pay.
    ```
    If you use stripe in sandbox mode you can use this information
    Number of card: 4242 4242 4242 4242
    Expiration date: <any in the future>
    CVC: <any three digit number>
    ```
-   After validate the payment you will see a confirmation page and receive an email who contains an invoice and a qrcode to show to the seller to retrieve the order

#### From the seller

-   Firstly you need to be logged on the backoffice
-   Now you can scan the qrcode from the client or alternatively identify the order with the reference of the invoice of the order in the dedicated section of backoffice
-   The two previous way will give you to a page where you can see the content of the order. On this page you can validate the delivery or refund it.

### Add a new product

-   Firstly you need to be logged on the backoffice
-   Go on the page section
-   Click on the button "Add" at the top right of the page
-   You will now see a form, type the following informations and validate:
    ```
    Page title: <the title you want to give to the product>
    Folder name: <should be completed automaticaly>
    Parent page: <leave root>
    Parent template: <select Product>
    Visible: <leave auto>
    ```
-   Now your are on a more complex form with different tab:
-   On tab "Contenu" fill the whole form with the information you want, but on the field "Lien de la liste des produits" select "Nos produits". You can't save the photo now, you need to do further. Go to the next tab without save.
-   On tab "Options" go in the section "Taxonomies" on the field "type" and type product. After go on section "Sitemap" on field "Sitemap priority" and select 0.8. Go on the next tab without save
-   On tab "Advanced" go in section "Ordering" on the field "Folder Numeric Prefix" and click on "Disabled". Now save by clicking on "Save" at the top right of the page
-   Return on the tab "Contenu" and set a photo to the field "Photo du produit" and save again.

### Remove a product from sale

You should not remove a page of a product. In place you should unpublished the page of the product.

To unpublish the product you should select the page of the product in backoffice. Go to tab "Options" on the section "Publishing" on the field "Published" and select "No" and save the change.

## Troubleshooting

In some case in the step "Configure the connection with the database" you can see the message "Connection Refused" after save the information despite type good credential for the database. This issue is cause by slow initialisation of mysql docker container. The solution to this issue is to wait and fill the database form again.

It is important to save the informations without the error message because the creation of the database tables is trigger when you save the information if the tables don't alreay exist. So the error message appearance means that the required tables are not created and don't allow the website to work normally.
