<?php
/**
 * Copyright © 2022 PayU Financial Services. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayU\Gateway\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use PayU\Api\Amount;
use PayU\Api\Transaction;
use PayU\Gateway\Gateway\SubjectReader;

/**
 * class PayUAdapterFactory
 * @package PayU\Gateway\Model\Adapter
 */
class RefundDataBuilder implements BuilderInterface
{
    public const TRANSACTION = 'transaction';
    public const PAYU_REFERENCE = 'payuReference';
    public const MERCHANT_REFERENCE = 'merchantReference';

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(private readonly SubjectReader $subjectReader)
    {
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();

        $transactionId = $payment->getCcTransId();

        if (!$transactionId) {
            throw new LocalizedException(__('No capture transaction to proceed refund.'));
        }

        $amount = new Amount();
        $amount->setCurrency($order->getCurrencyCode())
            ->setTotal($this->subjectReader->readAmount($buildSubject));

        $transaction = new Transaction();
        $transaction->setAmount($amount);

        return [
            self::TRANSACTION => $transaction,
            self::PAYU_REFERENCE => $transactionId,
            self::MERCHANT_REFERENCE => $order->getOrderIncrementId(),
        ];
    }
}
