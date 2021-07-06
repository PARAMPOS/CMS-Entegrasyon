<?php

namespace Param\CcppCreditCard\Gateway\Commands;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CaptureCommand
 *
 * @package Param\CcppCreditCard\Gateway\Commands
 */
class CaptureCommand implements CommandInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var HandlerInterface
     */
    private $handler;

    /**
     * CaptureCommand constructor.
     *
     * @param \Magento\Payment\Gateway\Validator\ValidatorInterface $validator
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Payment\Gateway\Response\HandlerInterface $handler
     */
    public function __construct(
        ValidatorInterface $validator,
        LoggerInterface $logger,
        HandlerInterface $handler
    ) {
        $this->validator = $validator;
        $this->logger = $logger;
        $this->handler = $handler;
    }

    /**
     * @param array $commandSubject
     *
     * @return \Magento\Payment\Gateway\Command\ResultInterface|null|void
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */

        $result = $this->validator->validate($commandSubject);

        if (!$result) {
            $this->logExceptions($result->getFailsDescription());
            throw new CommandException(
                __('Transaction has been declined. Please try again later.')
            );
        }

        $this->handler->handle(
            $commandSubject,
            $commandSubject
        );
    }

    /**
     * @param Phrase[] $fails
     * @return void
     */
    private function logExceptions(array $fails)
    {
        foreach ($fails as $failPhrase) {
            $this->logger->critical((string) $failPhrase);
        }
    }
}