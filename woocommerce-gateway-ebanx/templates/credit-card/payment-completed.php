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
    <p><?=$t['installments']?></p>
<?php else : ?>
    <p><?=$t['single_installment']?></p>
<?php endif; ?>
