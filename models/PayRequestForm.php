<?php

namespace app\models;

use splynx\models\finance\payments\BasePaymentProformaInvoice;

/**
 * Class PayRequestForm.
 * @package app\models
 */
class PayRequestForm extends BasePaymentProformaInvoice
{
    const SESSION_PAYMENT_ID = 'request_jcc_payment_id';
    const BANK_STATEMENT_ID = 'request_bank_statement_id';

    use BaseJccPaymentTrait;
}
