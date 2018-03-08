<?php

class WC_EBANX_Errors {
	/**
	 * The possible errors that EBANX can throw
	 *
	 * @return array An error array by user country
	 */
	public static function get_errors() {
		return array(
			'pt-br' => array(
				'GENERAL'                    => 'Não foi possível concluir a compra. Por favor, tente novamente ou entre em contato com o site.',
				'BP-DPAR-4'                  => 'Invalid integration key.',
				'BP-DR-13'                   => 'Informe o seu nome completo.',
				'BP-DR-15'                   => 'Informe um email válido.',
				'BP-DR-19'                   => 'Informe a sua data de nascimento no formato dia/mes/ano, por exemplo, 30/10/1980.',
				'BP-DR-20'                   => 'Informe a sua data de nascimento no formato dia/mes/ano, por exemplo, 30/10/1980.',
				'BP-DR-22'                   => 'O número de documento é obrigatório.',
				'BP-DR-23'                   => 'O número de documento informado é inválido.',
				'BP-DR-24'                   => 'Informe o seu CEP.',
				'BP-DR-25'                   => 'Informe o seu endereço.',
				'BP-DR-26'                   => 'O número da sua residência é obrigatório.',
				'BP-DR-27'                   => 'Informe a sua cidade.',
				'BP-DR-28'                   => 'Informe o seu estado.',
				'BP-DR-29'                   => 'Informe um estado do Brasil válido.',
				'BP-DR-30'                   => 'O país deve ser Brasil.',
				'BP-DR-31'                   => 'Informe um telefone celular.',
				'BP-DR-32'                   => 'O seu telefone celular deve ser um número válido.',
				'BP-DOC-01'                   => 'Seu nome, cpf e data de nascimento não coincidem, por favor, revise seus dados e tente novamente.',
				'BP-DR-39'                   => 'Seu nome, cpf e data de nascimento não coincidem, por favor, revise seus dados e tente novamente.',
				'BP-DR-40'                   => 'Você atingiu o limite de pagamento.',
				'BP-DR-48'                   => 'Preencha as informações de cartão de crédito.',
				'BP-DR-49'                   => 'Insira o número do cartão de crédito.',
				'BP-DR-51'                   => 'Insira o nome que está impresso no cartão de crédito.',
				'BP-DR-52'                   => 'O nome do cartão deve ter até 50 caracteres.',
				'BP-DR-54'                   => 'Digite o CVV que está impresso no cartão.',
				'BP-DR-55'                   => 'Digite o CVV corretamente.',
				'BP-DR-56'                   => 'Digite a data de validade do seu cartão.',
				'BP-DR-57'                   => 'A sua data deve estar no formato mes/ano, por exemplo, 12/2020.',
				'BP-DR-59'                   => 'A data é inferior a permitida.',
				'BP-DR-61'                   => 'Não foi possível utilizar este cartão de crédito. Contate o site informando este código: BP-DR-61.',
				'BP-DR-75'                   => 'O número do cartão de crédito é inválido.',
				'BP-DR-77'                   => 'Este país não está habilitado.',
				'BP-DR-78'                   => 'Este site não vende para este país.',
				'BP-DR-79'                   => 'O número de parcelas não é permitido, por favor, escolha outro.',
				'BP-DR-83'                   => 'Identificamos que seu cartão é estrangeiro, por favor, use outro.',
				'BP-DR-84'                   => 'Identificamos que esta compra já foi processada anteriormente.',
				'BP-DR-89'                   => 'O número de parcelas não é permitida, entre em contato com o site.',
				'BP-DR-95'                   => 'O nome impresso no cartão não é válido, número não são permitidos.',
				'BP-DR-97'                   => 'Compras parceladas não são permitidas em cartões pré pagos.',
				'BP-DR-98'                   => 'O país relacionado ao email digitado não corresponde ao país do método de pagamento.',
				'BP-DR-100'                  => 'Compras parceladas não são permitidas em cartões de débito.',
				'MISSING-CARD-PARAMS'        => 'Verifique se os dados do cartão de crédito estão corretos.',
				'MISSING-DEVICE-FINGERPRINT' => 'Algo aconteceu e não conseguimos concluir a sua compra. Por favor tente novamente.',
				'MISSING-CVV'                => 'Por favor digite o CVV do seu cartão de crédito.',
				'MISSING-INSTALMENTS'        => 'Por favor escolha em quantas parcelas você quer pagar.',
				'MISSING-BANK-NAME'          => 'Escolha um banco que deseja efetuar a sua compra.',
				'MISSING-VOUCHER'            => 'Escolha o tipo de voucher que deseja para efetuar a sua compra.',
				'INVALID-SAFETYPAY-TYPE'     => 'Escolha uma opção para o método de pagamento SafetyPay.',
				'INVALID-FIELDS'             => 'Alguns campos não foram preenchidos corretamente. Por favor, verifique e tente novamente.',
				'INVALID-BILLING-COUNTRY'    => 'Por favor, escolha um país.',
				'INVALID-ADDRESS'            => 'Insira o seu endereço completo com o número da casa, apartamento ou estabelecimento.',
				'REFUSED-CC'                 => 'Não foi possível concluir a compra. Entre em contato com o banco/emissor do cartão ou tente novamente.',
				'SANDBOX-INVALID-CC-NUMBER'  => 'Detectamos que você está em modo Sandbox e por isso só permitimos apenas alguns números de cartões. <a href="https://www.ebanx.com/business/en/developers/integrations/testing/credit-card-test-numbers" target="_blank">Você pode utilizar um dos nossos cartões de teste acessando a EBANX Developer\'s Academy.</a>'
			),
			'es'    => array(
				'GENERAL'                    => 'No pudimos concluir tu compra. Por favor intenta nuevamente o entra en contacto con el sitio web.',
				'BP-DR-6'                    => 'Para este opción de pago, el valor mínimo permitido es de %s. Elige otro método y finaliza tu pago.',
				'BP-DR-13'                   => 'Por favor, escribe tu nombre completo.',
				'BP-DR-15'                   => 'El email no es válido. ',
				'BP-DR-19'                   => 'Escribe tu fecha de nacimiento en el formato DD/MM/AA.',
				'BP-DR-20'                   => 'Escribe tu fecha de nacimiento en el formato DD/MM/AA.',
				'BP-DR-22'                   => 'El numero de documento es obligatorio.',
				'BP-DR-23'                   => 'El numero de documento no es valido.',
				'BP-DR-24'                   => 'Por favor, escribe tu código postal.',
				'BP-DR-25'                   => 'Por favor, escribe tu dirección.',
				'BP-DR-26'                   => 'Tu número de residencia es obligatorio.',
				'BP-DR-27'                   => 'Por favor, dinos tu ciudad de residencia.',
				'BP-DR-28'                   => 'Por favor, dinos tu estado de residencia.',
				'BP-DR-29'                   => 'Escribe un estado válido.',
				'BP-DR-30'                   => 'Tú país debe ser Brazil.',
				'BP-DR-31'                   => 'Por favor, dinos tu número de celular.',
				'BP-DR-32'                   => 'El número de teléfono no es válido. Intenta de nuevo.',
				'BP-DOC-01'                   => 'Seu nome, cpf e data de nascimento não coincidem, por favor, revise seus dados e tente novamente.',
				'BP-DR-39'                   => 'Seu nome, cpf e data de nascimento não coincidem, por favor, revise seus dados e tente novamente.',
				'BP-DR-40'                   => 'Disculpa, has alcanzado tu límite de compra.',
				'BP-DR-48'                   => 'Preencha as informações de cartão de crédito.',
				'BP-DR-49'                   => 'Por favor, introduce el número de tarjeta de crédito.',
				'BP-DR-51'                   => 'Por favor, introduce el nombre como está en tu tarjeta de crédito.',
				'BP-DR-52'                   => 'El nombre en la tarjeta no debe superar los 50 caracteres.',
				'BP-DR-54'                   => 'Por favor, introduce el CVV impreso en la tarjeta.',
				'BP-DR-55'                   => 'Por favor, introduce el CVV correctamente.',
				'BP-DR-56'                   => 'Por favor, introduce la fecha de vencimiento de tu tarjeta.',
				'BP-DR-57'                   => 'Por favor, escribe la fecha en el formato MM/AAAA',
				'BP-DR-59'                   => 'Por favor, introduce una fecha válida.',
				'BP-DR-61'                   => 'Disculpa pero no fue posible procesar tu tarjeta de crédito. Contacta el sitio web informado este código: BP-DR-61.',
				'BP-DR-75'                   => 'El número de tarjeta de crédito es inválido.',
				'BP-DR-77'                   => 'Disculpa, el país que has declarado no está habilitado.',
				'BP-DR-78'                   => 'Disculpa, aun no vendemos en el país que declaraste.',
				'BP-DR-79'                   => 'Disculpa, selecciona otro número de meses sin intereses.',
				'BP-DR-83'                   => 'Disculpa, no aceptamos tarjetas de crédito extranjeros. Por favor usa otro.',
				'BP-DR-84'                   => 'Esta compra ya fue procesada.',
				'BP-DR-89'                   => 'El número de meses sin intereses seleccionado es inválido. Entra en contacto con el sitio web.',
				'BP-DR-95'                   => 'El nombre escrito en la tarjeta no es válido. Números no son permitidos.',
				'BP-DR-97'                   => 'Disculpa, la opción de pago "meses sin intereses" no es permitida para tarjetas pre-pago.',
				'BP-DR-98'                   => 'O país relacionada o correo electrónico digitados no corresponde el método del pago.',
				'BP-DR-100'                  => 'Disculpa, la opción de pago "meses sin intereses" no es permitida para tarjetas de débito.',
				'MISSING-CARD-PARAMS'        => 'Por favor, verifica que la información de la tarjeta esté correcta.',
				'MISSING-DEVICE-FINGERPRINT' => 'Hemos encontrado un error y no fue posible concluir la compra. Por favor intenta de nuevo.',
				'MISSING-CVV'                => 'Por favor, introduce el CVV de tu tarjeta de crédito.',
				'MISSING-INSTALMENTS'        => 'Por favor, escoge en cuántos meses sin intereses deseas pagar.',
				'MISSING-BANK-NAME'          => 'Por favor, escoge el banco para finalizar la compra.',
				'MISSING-VOUCHER'            => 'Por favor, escoge el tipo de voucher que desea para finalizar la compra.',
				'INVALID-SAFETYPAY-TYPE'     => 'Por favor, escoge una opción para el método de pago SafetyPay.',
				'INVALID-FIELDS'             => 'Algunos campos no fueron llenados correctamente. Por favor verifica e inténtalo de nuevo.',
				'INVALID-BILLING-COUNTRY'    => 'Por favor, escoge un país.',
				'INVALID-ADDRESS'            => 'Por favor, introduce tu dirección completa. Número de residencia o apartamento.',
				'REFUSED-CC'                 => 'No pudimos concluir tu compra. Ponte en contacto con el banco/emisor de la tarjeta o vuelve a intentarlo.',
				'SANDBOX-INVALID-CC-NUMBER'  => 'Detectamos que estás en modo Sandbox y por eso restringimos algunos números de tarjetas. <a href="https://www.ebanx.com/business/en/developers/integrations/testing/credit-card-test-numbers" target="_blank">Puedes utilizar una de nuestras tarjetas de prueba accediendo a EBANX Developer\'s Academy.</a>'
			),
		);
	}

	/**
	 * Get the error message
	 *
	 * @param Exception $exception
	 * @param string $country
	 * @return string
	 */
	public static function get_error_message($exception, $country)
	{
		$code = $exception->getCode() ?: $exception->getMessage();

		$languages = array(
			'ar' => 'es',
			'mx' => 'es',
			'cl' => 'es',
			'pe' => 'es',
			'co' => 'es',
			'br' => 'pt-br',
		);
		$language = $languages[$country];

		$errors = static::get_errors();

		if ($code === 'BP-DR-6' && $language === 'es') {
			$error_info = array();
			preg_match('/Amount must be greater than (\w{3}) (.+)/',
				$exception->getMessage(),
				$error_info
			);
			$amount = $error_info[2];
			$currency = $error_info[1];
			return sprintf($errors[$language][$code], wc_price($amount, array('currency' => $currency)));
		}

		return !empty($errors[$language][$code]) ? $errors[$language][$code] : $errors[$language]['GENERAL'] . " ({$code})";
	}
}
