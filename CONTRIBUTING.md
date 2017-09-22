[![Codacy Badge](https://api.codacy.com/project/badge/Grade/09ef5eb63a394dc3b76cb4319129fbf3)](https://www.codacy.com/app/EBANX/woocommerce-gateway-ebanx?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=ebanx/woocommerce-gateway-ebanx&amp;utm_campaign=Badge_Grade)

# EBANX Payment Gateway for WooCommerce

This plugin enables you to integrate your WooCommerce store with the EBANX payment gateway.

Please, visit the [official plugin page on WordPress store](https://wordpress.org/plugins/ebanx-payment-gateway-for-woocommerce/).

## Introduction
We've put together all the information you'll need on this file. Also, we always try to keep this file updated and with as much information as possible. If you want to help us with this file or with any coding issue, we would really appreciate it. :heart:

## Getting Started with Docker

Don't you know what is Docker? [Know here](https://www.docker.com/what-docker).

You need to install Docker on your machine before start. [Please install the CE Edition of your OS](https://www.docker.com/community-edition). 

#### Disable built-in Apache server (Mac OS only)

```
sudo launchctl unload -w /System/Library/LaunchDaemons/org.apache.httpd.plist
```

#### Create the containers

After installed Docker and disabled apache, clone this repo, go to root folder and run:

```
docker-compose up
```

**The first installation may take up to 5 minutes. After that, visit `http://localhost` on your browser. We use the port 80 and 3306. So, check if you are not using these ports.**

Every time that you want to go back to plugin development, just run `docker-compose up`.

To run docker in background, execute `docker-compose up -d`, and `docker-compose stop` to stop the application.

To login into Wordpress, visit `http://localhost/wp-admin/`.

The credentials are: 

```
Username: ebanx
Password: ebanx
```

**To change these informations, you can edit the environments on file `docker-compose.yml`.**

The defaults are:

```
MYSQL_DATABASE: wordpress
MYSQL_ROOT_PASSWORD: root

WORDPRESS_DB_NAME: wordpress
WORDPRESS_DB_USER: root
WORDPRESS_DB_PASSWORD: root
WORDPRESS_DB_HOST: mysql

EBANX_WC_PLUGIN_VERSION: 3.0.5
EBANX_ADMIN_USERNAME: ebanx
EBANX_ADMIN_PASSWORD: ebanx
EBANX_SITE_TITLE: EBANX
EBANX_SITE_EMAIL: plugin@ebanx.com
```

#### What did we do here?

The Docker installed Wordpress, MySQL, PHP, WooCommerce, create some products and pages, installed the EBANX plugin and another things.

To know more about the Docker commands, [please read this gist](https://gist.github.com/cezarlz/cf9ecbd8be33562b16d07fc1bc04b150).

## Have you found a bug?

You can create a new [Issue](https://github.com/ebanx/woocommerce-gateway-ebanx/issues/new) and wait for someone to fix it. Keep in mind that if you don't provide enough information no one will be able to help you.

## So you want to code...

If you want to code (and you are not an ebanker [yet](https://ebanx.recruiterbox.com/)) all you have to do is fork our repo create a [well named]branch from develop and make a [pull request](https://github.com/ebanx/woocommerce-gateway-ebanx/compare) to our develop.