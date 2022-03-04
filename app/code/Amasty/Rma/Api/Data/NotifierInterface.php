<?php

namespace Amasty\Rma\Api\Data;

interface NotifierInterface
{
    public function notify(
        \Amasty\Rma\Api\Data\RequestInterface $request,
        \Amasty\Rma\Api\Data\MessageInterface $message
    ): void;
}
