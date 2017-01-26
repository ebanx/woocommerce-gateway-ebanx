<?php
/**
 * Bank Slip - Payment instructions.
 *
 * @author  EBANX
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="banking-ticket__desc">
    <p class="woocommerce-thankyou-order-received">Pronto, seu boleto foi gerado, <?php echo $customer_name?>.</p>
    <p>Mandamos uma cópia para o email <strong><?php echo $customer_email ?></strong>.</p>
    <p>
        - Não se esqueça, ele vence dia <strong><?php echo date_i18n('d/m', strtotime($due_date))?></strong>. Depois disso não é mais possível realizar o pagamento.<br/>
        - Dica: Além dos bancos e casas lotéricas você também pode pagar pelo seu internet banking, sem sair de casa.
    </p>
    <p>Ficou alguma dúvida? A gente te <a href="https://www.ebanx.com/br/ajuda/pagamentos/boleto-bancario" target="_blank">ajuda</a>.</p>
</div>

<hr>

<div class="banking-ticket__barcode">
    <div class="banking-ticket__barcode-code">
        <?php echo $barcode_fraud['boleto1']; ?>.<?php echo $barcode_fraud['boleto2']; ?> <?php echo $barcode_fraud['boleto3']; ?>.<?php echo $barcode_fraud['boleto4']; ?> <?php echo $barcode_fraud['boleto5']; ?>.<?php echo $barcode_fraud['boleto6']; ?> <?php echo $barcode_fraud['boleto7']; ?> <?php echo $barcode_fraud['boleto8']; ?>
    </div>
    <div class="banking-ticket__barcode-copy">
        <button type="button" class="button ebanx-button--copy" data-clipboard-text="<?php echo $barcode; ?>">Copiar</button>
    </div>
</div>

<hr>

<div class="banking-ticket__actions">
    <div class="ebanx-button--group ebanx-button--group-two">
        <a href="<?php echo $url_pdf ?>" target="_blank" class="button banking-ticket__action">Salvar em PDF</a>
        <a href="<?php echo $url_print ?>" target="_blank" class="button banking-ticket__action">Imprimir boleto</a>
    </div>
</div>

<div>
    <iframe id="ebanx-boleto-frame" src="<?php echo $url_iframe; ?>" style="width: 100%; border: 0px; height: 1000px"></iframe>
</div>
