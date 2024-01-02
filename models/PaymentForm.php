<?php

namespace app\models;

use splynx\models\finance\payments\BasePaymentModel;

/**
 * Class PaymentForm.
 * @package app\models
 */
class PaymentForm extends BasePaymentModel
{
    const SESSION_PAYMENT_ID = 'jcc_payment_id';
    const BANK_STATEMENT_ID = 'bank_statement_id';

    use BaseJccPaymentTrait;
}
