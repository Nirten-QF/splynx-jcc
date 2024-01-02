<?php

namespace app\models;

use splynx\base\BaseApiModel;
use splynx\helpers\ApiHelper;
use splynx\helpers\ConfigHelper;
use yii\base\InvalidCallException;

class BankStatement extends BaseApiModel
{
    public $id;
    public $customer_id;
    public $payment_date;
    public $amount;
    public $status = self::STATUS_NEW;
    public $payment_id;

    const STATUS_NEW = 'new';
    const STATUS_PROCESSED = 'processed';
    const STATUS_ERROR = 'error';
    const STATUS_CANCELED = 'canceled';

    public static $statementsApiCall = 'admin/finance/bank-statements';
    public static $statementsRecordsApiCall = 'admin/finance/bank-statements-records';

    public function create()
    {
        $processId = $this->getStatementProcessId();

        $params = [
            'bank_statements_process_id' => $processId,
            'amount' => $this->amount,
            'status' => $this->status,
            'customer_id' => $this->customer_id,
            'payment_date' => $this->payment_date,
            'comment' => 'Jcc'
        ];

        $result = ApiHelper::getInstance()->post(self::$statementsRecordsApiCall, $params);
        if ($result['result'] == false) {
            return false;
        }

        $this->id = $result['response']['id'];

        return $this->id;
    }

    public function update()
    {
        $params = [
            'customer_id' => $this->customer_id,
            'payment_date' => $this->payment_date,
            'amount' => $this->amount,
            'status' => $this->status,
            'payment_id' => $this->payment_id,
        ];

        $result = ApiHelper::getInstance()->put(self::$statementsRecordsApiCall, $this->id, $params);

        if ($result['result'] == false) {
            throw new InvalidCallException('Error in API Call!');
        }

        return true;
    }

    public static function get($id)
    {
        $result = ApiHelper::getInstance()->get(self::$statementsRecordsApiCall, $id);

        if ($result['result'] == false) {
            throw new InvalidCallException('Error in API Call!');
        }

        $model = new self;
        self::populate($model, $result['response']);
        return $model;
    }

    private function getStatementProcessTitle()
    {
        $period = ConfigHelper::get('bank_statements_group');

        if ($period == 'day') {
            $comment = date('d/m/Y');
        } else {
            $comment = date('m/Y');
        }

        return 'Jcc ' . $comment;
    }

    private function getStatementProcessId()
    {
        $title = $this->getStatementProcessTitle();

        $params = [
            'main_attributes' => [
                'title' => $title
            ]
        ];

        $result = ApiHelper::getInstance()->search(self::$statementsApiCall, $params);

        if ($result['result'] == false) {
            throw new InvalidCallException('Error in API Call!');
        }

        if (!empty($result['response'])) {
            $item = reset($result['response']);
            return $item['id'];
        }

        // Create new
        $params = [
            'title' => $title,
            'status' => 'success'
        ];

        $result = ApiHelper::getInstance()->post(self::$statementsApiCall, $params);
        if ($result['result'] == false) {
            throw new InvalidCallException('Error in API Call!');
        }

        return $result['response']['id'];
    }
}
