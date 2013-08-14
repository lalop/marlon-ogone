<?php

namespace Ogone;

use InvalidArgumentException;
use DateTime;
use RuntimeException;

class SubscriptionPaymentRequest extends PaymentRequest {

    private $requiredSubscriptionFields = array(
        'SUBSCRIPTION_ID', 'SUB_AMOUNT', 'SUB_COM', 'SUB_ORDERID', 'SUB_PERIOD_UNIT',
        'SUB_PERIOD_NUMBER', 'SUB_PERIOD_MOMENT','SUB_STARTDATE', 'SUB_ENDDATE', 'SUB_STATUS'
    );

    /**
     * Set amount in cents, eg EUR 12.34 is written as 1234
     * For subscriptions an amount of 0 can be selected, however this feature must first be enabled by ogone for your account
     */
    public function setAmount($amount)
    {
        if(!is_int($amount)) {
            throw new InvalidArgumentException("Integer expected. Amount is always in cents");
        }
        if($amount < 0) {
            throw new InvalidArgumentException("Amount must be a positive number or 0");
        }
        if($amount >= 1.0E+15) {
            throw new InvalidArgumentException("Amount is too high");
        }

        $this->parameters['amount'] = $amount;

    }

    /**
     * Unique identifier of the subscription. The subscription id must be assigned dynamically.
     * @author René de Kat <renedekat@9lives-development.com>
     *
     * @param string $subscriptionId (maxlength 50)
     */
    public function setSubscriptionId($subscriptionId)
    {
        if(strlen($subscriptionId) > 50) {
            throw new InvalidArgumentException("Subscription id cannot be longer than 50 characters");
        }
        if(preg_match('/[^a-zA-Z0-9_-]/', $subscriptionId)) {
            throw new InvalidArgumentException("Subscription id cannot contain special characters");
        }
        $this->parameters['SUBSCRIPTION_ID'] = $subscriptionId;
    }

    /**
     * Amount of the subscription (can be different from the amount of the original transaction)
     * multiplied by 100, since the format of the amount must not contain any decimals or other separators.
     *
     * @author René de Kat <renedekat@9lives-development.com>
     *
     * @param integer $amount
     */
    public function setSubscriptionAmount($amount)
    {
        if(!is_int($amount)) {
            throw new InvalidArgumentException("Integer expected. Amount is always in cents");
        }
        if($amount <= 0) {
            throw new InvalidArgumentException("Amount must be a positive number");
        }
        if($amount >= 1.0E+15) {
            throw new InvalidArgumentException("Amount is too high");
        }
        $this->parameters['SUB_AMOUNT'] = $amount;
    }

    /**
     * Order description
     * @author René de Kat <renedekat@9lives-development.com>
     *
     * @param string $description (maxlength 100)
     */
    public function setSubscriptionDescription($description)
    {
        if(strlen($description) > 100) {
            throw new InvalidArgumentException("Subscription description cannot be longer than 50 characters");
        }
        if(preg_match('/[^a-zA-Z0-9_- ]/', $description)) {
            throw new InvalidArgumentException("Subscription description cannot contain special characters");
        }
        $this->parameters['SUB_COM'] = $description;
    }

    /**
     * OrderID for subscription payments
     * @author René de Kat <renedekat@9lives-development.com>
     *
     * @param string $orderId (maxlength 40)
     */
    public function setSubscriptionOrderId($orderId)
    {
        if(strlen($orderId) > 40) {
            throw new InvalidArgumentException("Subscription order id cannot be longer than 40 characters");
        }
        if(preg_match('/[^a-zA-Z0-9_-]/', $orderId)) {
            throw new InvalidArgumentException("Subscription order id cannot contain special characters");
        }
        $this->parameters['SUB_ORDERID'] = $orderId;
    }

    /**
     * Set subscription payment interval
     * @author René de Kat <renedekat@9lives-development.com>
     *
     * @param string $unit 			(‘d’ = daily, ‘ww’ = weekly, ‘m’ = monthly)
     * @param integer $interval 	Interval between each occurrence of the subscription payments.
     * @param integer $moment		Depending on sub_period_unit
     *								Daily (d)
     *								interval in days
     *								Weekly (ww)
     * 								1=Sunday, … 7=Saturday
     *								Monthly (m)
     * 								day of the month
     */
    public function setSubscriptionPeriod($unit, $interval, $moment)
    {
        // Check unit
        if (!in_array($unit, array('d', 'ww', 'm'))) {
            throw new InvalidArgumentException("Subscription period unit should be d (daily), ww (weekly) or m (monthly)");
        }
        $this->parameters['SUB_PERIOD_UNIT'] = $unit;


        // Check interval
        if(!is_int($interval)) {
            throw new InvalidArgumentException("Integer expected for interval");
        }
        if($interval < 0) {
            throw new InvalidArgumentException("Interval must be a positive number > 0");
        }
        if($interval >= 1.0E+15) {
            throw new InvalidArgumentException("Interval is too high");
        }
        $this->parameters['SUB_PERIOD_NUMBER'] = $interval;

        // Check moment
        if(!is_int($moment)) {
            throw new InvalidArgumentException("Integer expected for moment");
        }
        if($moment <= 0) {
            throw new InvalidArgumentException("Moment must be a positive number");
        }

        if ('ww' == $unit) {
            // Valid values are 1 to 7
            if ($moment > 7) {
                throw new InvalidArgumentException("Moment should be 1 (Sunday), 2, 3 .. 7 (Saturday)");
            }
        } elseif ('m' == $unit) {
            // We will not allow a day of month > 28
            if ($moment > 28) {
                throw new InvalidArgumentException("Moment can't be larger than 29. Last day for month allowed is 28.");
            }
        }
        $this->parameters['SUB_PERIOD_MOMENT'] = $moment;
    }


    /**
     * Subscription start date
     * @author René de Kat <renedekat@9lives-development.com>
     *
     * @param DateTime $data 	Startdate of the subscription.
     */
    public function setSubscriptionStartdate(DateTime $date)
    {
        $this->parameters['SUB_STARTDATE'] = $date->format('Y-m-d');
    }

    /**
     * Subscription end date
     * @author René de Kat <renedekat@9lives-development.com>
     *
     * @param DateTime $data 	Enddate of the subscription.
     */
    public function setSubscriptionEnddate(DateTime $date)
    {
        $this->parameters['SUB_ENDDATE'] = $date->format('Y-m-d');
    }

    /**
     * Set subscription status
     * @author René de Kat <renedekat@9lives-development.com>
     *
     * @param integer $status	0 = inactive, 1 = active
     */
    public function setSubscriptionStatus($status)
    {
        if (!in_array($status, array(0, 1))) {
            throw new InvalidArgumentException("Invalid status specified for subscription. Possible values: 0 = inactive, 1 = active");
        }
        $this->parameters['SUB_STATUS'] = $status;
    }

    /**
     * Set comment for merchant
     * @author René de Kat <renedekat@9lives-development.com>
     *
     * @param string $comment
     */
    public function setSubscriptionComment($comment)
    {
        if(strlen($comment) > 200) {
            throw new InvalidArgumentException("Subscription comment cannot be longer than 200 characters");
        }
        if(preg_match('/[^a-zA-Z0-9_- ]/', $comment)) {
            throw new InvalidArgumentException("Subscription comment cannot contain special characters");
        }
        $this->parameters['SUB_COMMENT'] = $comment;
    }

    public function validate()
    {
        parent::validate();

        foreach($this->requiredSubscriptionFields as $field)
        {
            if(!isset($this->parameters[$field])) {
                throw new RuntimeException("$field can not be empty");
            }
        }
    }
}
