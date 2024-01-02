<?php

namespace app\models;

use splynx\models\finance\payments\BasePaymentInvoice;

/**
 * Class PayInvoiceForm.
 * @package app\models
 */
class PayInvoiceForm extends BasePaymentInvoice
{
    const SESSION_PAYMENT_ID = 'invoice_jcc_payment_id';
    const BANK_STATEMENT_ID = 'invoice_bank_statement_id';

    use BaseJccPaymentTrait;
}
