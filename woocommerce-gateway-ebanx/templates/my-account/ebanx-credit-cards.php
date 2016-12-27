<?php if (!empty($cards)): ?>

    <h3>Your saved credit cards</h3>

    <p>The following credit cards will be listed on the checkout page. To delete a credit card, just check it and submit.</p>

    <form method="post" action="" class="ebanx-credit-cards-form">
        <div class="ebanx-credit-cards">
            <?php foreach ($cards as $card): ?>
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

        <input type="submit" class="button" value="Delete cards">
    </form>

<?php else: ?>
    <h3>No credit cards found</h3>

    <p>To save a credit card, pay an order on checkout using credit card as payment method.</p>
<?php endif ?>
