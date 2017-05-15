#!/bin/sh

set -e

export WP_OWNER=www-data
export WP_GROUP=www-data
export WP_ROOT=/var/www/html
export WS_GROUP=www-data

/usr/local/bin/wait-for-it.sh -t 60 mysql:3306 -- echo 'MySQL is up!'

if ! $(wp core is-installed --allow-root); then
  cd $WP_ROOT

  if [ ! -e .htaccess ]; then
    cat > .htaccess <<-'EOF'
				# BEGIN WordPress
				<IfModule mod_rewrite.c>
				RewriteEngine On
				RewriteBase /
				RewriteRule ^index\.php$ - [L]
				RewriteCond %{REQUEST_FILENAME} !-f
				RewriteCond %{REQUEST_FILENAME} !-d
				RewriteRule . /index.php [L]
				</IfModule>
				# END WordPress
		EOF
		chown www-data:www-data .htaccess
  fi

  wp core install --url=localhost --title=$EBANX_SITE_TITLE --admin_user=$EBANX_ADMIN_USERNAME --admin_password=$EBANX_ADMIN_PASSWORD --admin_email=$EBANX_SITE_EMAIL --skip-email --allow-root --debug

  # Install and activate plugins
  wp plugin install woocommerce --version=$EBANX_WC_PLUGIN_VERSION --activate --allow-root --debug
  wp plugin activate woocommerce-gateway-ebanx --allow-root --debug

  # Install Pages
  wp post create --post_type=page --post_title='My Account' --post_status='publish' --post_content='[woocommerce_my_account]' --allow-root --debug
  wp post create --post_type=page --post_title='Cart' --post_status='publish' --post_content='[woocommerce_cart]' --allow-root --debug
  wp post create --post_type=page --post_title='Checkout' --post_status='publish' --post_content='[woocommerce_checkout]' --allow-root --debug
  wp post create --post_type=page --post_title='Shop' --post_status='publish' --allow-root --debug

  # Configure WooCommerce settings
  wp db query 'UPDATE wp_options SET option_value="US:NY" WHERE option_name="woocommerce_default_country"' --allow-root --debug
  wp db query 'UPDATE wp_options SET option_value="USD" WHERE option_name="woocommerce_currency"' --allow-root --debug
  wp db query 'UPDATE wp_options SET option_value="3" WHERE option_name="woocommerce_myaccount_page_id"' --allow-root --debug
  wp db query 'UPDATE wp_options SET option_value="4" WHERE option_name="woocommerce_cart_page_id"' --allow-root --debug
  wp db query 'UPDATE wp_options SET option_value="5" WHERE option_name="woocommerce_checkout_page_id"' --allow-root --debug
  wp db query 'UPDATE wp_options SET option_value="6" WHERE option_name="woocommerce_shop_page_id"' --allow-root --debug

  # Create a product
  wp wc product create --name='Jeans' --status='publish' --regular_price='250' --user=1 --allow-root --debug

  # Configure Permalink
  wp rewrite structure '/%postname%/' --hard --allow-root

  find ${WP_ROOT} -exec chown ${WP_OWNER}:${WP_GROUP} {} \;
  find ${WP_ROOT} -type d -exec chmod 755 {} \;
  find ${WP_ROOT} -type f -exec chmod 644 {} \;
  chgrp ${WS_GROUP} ${WP_ROOT}/wp-config.php
  chmod 660 ${WP_ROOT}/wp-config.php
  find ${WP_ROOT}/wp-content -exec chgrp ${WS_GROUP} {} \;
  find ${WP_ROOT}/wp-content -type d -exec chmod 775 {} \;
  find ${WP_ROOT}/wp-content -type f -exec chmod 664 {} \;
  chmod g+w ${WP_ROOT}
  chown -R ${WP_OWNER}:${WP_GROUP} ${WP_ROOT}/wp-content
  chown -R ${WP_OWNER}:${WP_GROUP} ${WP_ROOT}/wp-content/plugins/
  chown -R ${WP_OWNER}:${WP_GROUP} ${WP_ROOT}/wp-content/uploads/
  chown -R ${WP_OWNER}:${WP_GROUP} ${WP_ROOT}/wp-content/themes/

  echo "EBANX: Visit http://localhost or http://localhost/wp-admin/"
  echo "EBANX: Username - $EBANX_ADMIN_USERNAME"
  echo "EBANX: Password - $EBANX_ADMIN_PASSWORD"
fi

apache2-foreground