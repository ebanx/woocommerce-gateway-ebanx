<?php
/**
 * Credit Card - Payment completed.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<p><strong><?=$t['payment_approved'] ?></strong></p>
<p><?=$t['important_data'] ?></p>
<p><?=$t['total_amount'] ?> <?=WC_EBANX_Gateway_Utils::CURRENCY_CODE_USD ?> <?=$order_amount ?></p>
<?php if ($instalments_number > 1) : ?>
    <p><?=$instalments_number ?> <?=$t['instalments'] ?> <?=WC_EBANX_Gateway_Utils::CURRENCY_CODE_USD ?> <?=$instalments_amount ?></p>
<?php else : ?>
    <p>Pagamento à vista</p>
<?php endif; ?>
<p><?=$t['card_last_numbers'] ?> <?=$masked_card ?></p>
<p><?=$t['thanks_message'] ?></p>
