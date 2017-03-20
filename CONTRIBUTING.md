[![Codacy Badge](https://api.codacy.com/project/badge/Grade/09ef5eb63a394dc3b76cb4319129fbf3)](https://www.codacy.com/app/EBANX/woocommerce-gateway-ebanx?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=ebanx/woocommerce-gateway-ebanx&amp;utm_campaign=Badge_Grade)

# EBANX Payment Gateway for WooCommerce

This plugin enables you to integrate your WooCommerce store with the EBANX payment gateway.

Please, visit the [official plugin page on WordPress store](https://wordpress.org/plugins/ebanx-payment-gateway-for-woocommerce/).

## Getting Started

If you want to contribute to our repository the best way to do it is cloning it to a different folder than wordpress and creating a symbolic link:

1. Install [WordPress](https://codex.wordpress.org/Installing_WordPress) to your prefered location:
	1. With your terminal `cd` into your web root folder.
		eg: If your web root folder is `/var/www/html`
		then: `cd /var/www/html`
		> In this guide we're going to write only `/var/www/html` when refering to your web root folder. If your folder is different just change it on every command.
		
	2. Download the latest WordPress build: `wget https://wordpress.org/latest.tar.gz`
	3. Unzip the just downloaded file: `tar -xzvf latest.tar.gz`
	4. Delete that file since we don't need it anymore: `rm latest.tar.gz`
	5. Create the Database and a User
	6. Access `http://localhost/wordpress`(it might be different depending on the folder you've downloaded WordPress) and follow its steps.
2. Install WooCommerce. If you've configured FTP you can access WordPress Admin Dashboard and install it automatically. Else, you can follow these steps:
	1. Go to the plugins folder on your WordPress: `cd /var/www/html/wp-content/plugins`
	2. Download the lastest WooCommerce build: `git clone https://github.com/woocommerce/woocommerce.git`
	3. Configure it on WordPress Admin Dashboard
3. Clone this repository to another folder outsite WordPress folder: `git clone https://github.com/ebanx/woocommerce-gateway-ebanx`
4. Go into that folder: `cd woocommerce-gateway-ebanx`. This is going to be your development root folder.
5. Create a symlink to WordPress plugin folder: `ln -s woocommerce-gateway-ebanx /var/www/html/wp-content/plugins/woocommerce-gateway-ebanx`
6. Let the coding begin!