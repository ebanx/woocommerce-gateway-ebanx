<?php
/**
 * Bank Slip - Payment instructions.
 *
 * @author  EBANX
 * @package WooCommerce_Pagarme/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<hr>

<div class="banking-ticket__desc">
    <p class="woocommerce-thankyou-order-received">Seu boleto foi gerado com sucesso</p>
    <p>Pague agora o seu boleto para liberar o seu pedido.</p>
    <p>O boleto também foi enviado para o email <strong><?=$customer_email ?></strong>.</p>
    <p>Dúvidas de como pagar seu boleto? <a href="#" target="_blank">Clique aqui</a>.</p>
</div>

<hr>

<div class="banking-ticket__barcode">
    <div class="banking-ticket__barcode-code">
        <?=$barcode_fraud['boleto1']; ?>.<?=$barcode_fraud['boleto2']; ?> <?=$barcode_fraud['boleto3']; ?>.<?=$barcode_fraud['boleto4']; ?> <?=$barcode_fraud['boleto5']; ?>.<?=$barcode_fraud['boleto6']; ?> <?=$barcode_fraud['boleto7']; ?> <?=$barcode_fraud['boleto8']; ?>
    </div>
    <div class="banking-ticket__barcode-copy">
        <button type="button" class="button ebanx-button--copy" data-clipboard-text="<?php echo $barcode; ?>">
            Copiar
            <span class="ebanx-button--copy-msg">Copiado com sucesso!</span>
        </button>
    </div>
</div>

<hr>

<div class="banking-ticket__actions">
    <div class="ebanx-button--group ebanx-button--group-two">
        <a href="<?=$url_pdf ?>" target="_blank" class="button banking-ticket__action">Salvar em PDF</a>
        <a href="<?=$url_print ?>" target="_blank" class="button banking-ticket__action">Imprimir boleto</a>
    </div>
</div>



<div>
    <iframe src="<?=$url_basic; ?>" style="width: 100%; height: 1000px; border: 0px;"></iframe>
</div>
