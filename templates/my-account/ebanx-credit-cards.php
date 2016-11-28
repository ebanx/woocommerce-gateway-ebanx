<?php if (!empty($cards)): ?>

    <p>The following credit cards will be listed on the checkout page. To delete a credit card, just check it and save.</p>

    <form method="post" action="">
        <div class="u-columns col2-set credit-cards">
            <?php foreach ($cards as $card): ?>
                <div class="u-column1 col-1 woocommerce-Address">
            		<label class="woocommerce-Address-title title">
            			<h3><input type="checkbox" name="credit-card-delete[]" value="<?php echo $card->masked_number ?>"> <?php echo ucfirst($card->brand) ?></h3>
                        <span><strong>Number: </strong><?php echo $card->masked_number ?></span>
                    </label>
            	</div>
            <?php endforeach ?>
        </div>

        <input type="submit" class="button" value="Delete and Save">
    </form>

<?php else: ?>
    <p>No credit cards found. To save a credit card, pay a order on checkout using one.</p>
<?php endif ?>
