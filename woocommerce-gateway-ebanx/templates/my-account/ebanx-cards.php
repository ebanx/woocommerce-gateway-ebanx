<?php if (empty($credit_cards) && empty($debit_cards)): ?>
    <h1><?php _e('No credit cards found.', 'woocommerce-gateway-ebanx'); ?></h1>
    <p><?php _e('To save a debit or credit card, pay an order on checkout using a card as payment method.', 'woocommerce-gateway-ebanx'); ?></p>
<?php endif ?>

<?php if (!empty($credit_cards)): ?>

    <h3><?php _e('Your saved credit cards', 'woocommerce-gateway-ebanx'); ?></h3>

    <p><?php _e('The following credit cards will be listed on the checkout page. To delete a credit card, just check it and submit.', 'woocommerce-gateway-ebanx'); ?></p>

    <form method="post" action="" class="ebanx-credit-cards-form">
        <div class="ebanx-credit-cards">
            <?php foreach ($credit_cards as $card): ?>
        		<label class="ebanx-credit-card">
                    <input type="checkbox" name="credit-card-delete[]" value="<?php echo $card->masked_number ?>" class="ebanx-delete-input">
        			<div class="ebanx-credit-card-info">
                        <div>
                            <img src="<?php echo PLUGIN_DIR_URL . "assets/images/icons/$card->brand.png" ?>" height="20" style="height: 20px; margin-left: 0; margin-right: 7px; float: none;" alt="<?php echo $card->brand ?>" class="ebanx-credit-card-brand">
                            <span class="ebanx-credit-card-brand-name"><?php echo ucfirst($card->brand) ?></span>
                        </div>
                        <p class="ebanx-credit-card-bin">&bull;&bull;&bull;&bull; <?php echo substr($card->masked_number, -4) ?></p>
                    </div>
                </label>
            <?php endforeach ?>
        </div>

        <input type="submit" class="button" value="<?php _e('Delete cards', 'woocommerce-gateway-ebanx'); ?>">
    </form>

<?php endif ?>

<?php if (!empty($debit_cards)): ?>

    <h3><?php _e('Your saved debit cards', 'woocommerce-gateway-ebanx'); ?></h3>

    <p><?php _e('The following debit cards will be listed on the checkout page. To delete a debit card, just check it and submit.', 'woocommerce-gateway-ebanx'); ?></p>

    <form method="post" action="" class="ebanx-credit-cards-form">
        <div class="ebanx-credit-cards">
            <?php foreach ($debit_cards as $card): ?>
        		<label class="ebanx-credit-card">
                    <input type="checkbox" name="debit-card-delete[]" value="<?php echo $card->masked_number ?>" class="ebanx-delete-input">
        			<div class="ebanx-credit-card-info">
                        <div>
                            <img src="<?php echo PLUGIN_DIR_URL . "assets/images/icons/$card->brand.png" ?>" height="20" style="height: 20px; margin-left: 0; margin-right: 7px; float: none;" alt="<?php echo $card->brand ?>" class="ebanx-credit-card-brand">
                            <span class="ebanx-credit-card-brand-name"><?php echo ucfirst($card->brand) ?></span>
                        </div>
                        <p class="ebanx-credit-card-bin">&bull;&bull;&bull;&bull; <?php echo substr($card->masked_number, -4) ?></p>
                    </div>
                </label>
            <?php endforeach ?>
        </div>

        <input type="submit" class="button" value="<?php _e('Delete cards', 'woocommerce-gateway-ebanx'); ?>">
    </form>
<?php endif ?>
