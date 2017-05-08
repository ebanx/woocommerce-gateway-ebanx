#!/bin/sh

set -e

/usr/local/bin/wait-for-it.sh -t 60 mysql:3306 -- echo "MySQL is up!"

if ! $(wp core is-installed --allow-root); then
  cd /var/www/html

  wp core install --url=localhost --title=$EBANX_SITE_TITLE --admin_user=$EBANX_ADMIN_USERNAME --admin_password=$EBANX_ADMIN_PASSWORD --admin_email=$EBANX_SITE_EMAIL --skip-email --allow-root --debug

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
    
    # Install and activate plugins
    wp plugin install woocommerce --version=$EBANX_WC_PLUGIN_VERSION --activate --allow-root
    wp plugin activate woocommerce-gateway-ebanx --allow-root 

    # Install Pages
    wp post create --post_type=page --post_title='My Account' --post_status='publish' --post_content='[woocommerce_my_account]' --allow-root
    wp post create --post_type=page --post_title='Cart' --post_status='publish' --post_content='[woocommerce_cart]' --allow-root
    wp post create --post_type=page --post_title='Checkout' --post_status='publish' --post_content='[woocommerce_checkout]' --allow-root
    wp post create --post_type=page --post_title='Shop' --post_status='publish' --allow-root
  
    # Configure WooCommerce settings
    wp db query 'UPDATE wp_options SET option_value="US:NY" WHERE option_name="woocommerce_default_country"' --allow-root
    wp db query 'UPDATE wp_options SET option_value="USD" WHERE option_name="woocommerce_currency"' --allow-root
    wp db query 'UPDATE wp_options SET option_value="4" WHERE option_name="woocommerce_cart_page_id"' --allow-root
    wp db query 'UPDATE wp_options SET option_value="6" WHERE option_name="woocommerce_shop_page_id"' --allow-root
    wp db query 'UPDATE wp_options SET option_value="5" WHERE option_name="woocommerce_checkout_page_id"' --allow-root

    # Create a product
    wp wc product create --name='Jeans' --status='publish' --regular_price='250' --user=1 --allow-root

    # Configure Permalink
    wp rewrite structure '/%postname%/' --hard --allow-root
  fi
fi

apache2-foreground