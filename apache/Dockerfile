FROM httpd:2.4-alpine

COPY ./my-httpd.conf /usr/local/apache2/conf/httpd.conf
COPY ./my-httpd-vhosts.conf /usr/local/apache2/conf/extra/httpd-vhosts.conf

COPY ./server.crt /usr/local/apache/conf/ssl.crt
COPY ./server.key /usr/local/apache/conf/ssl.key