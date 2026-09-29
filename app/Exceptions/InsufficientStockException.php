<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public string $productName,
        public int $requestedQuantity,
        public int $availableStock
    ) {
        parent::__construct(
            "Insufficient stock for '{$productName}'. Requested: {$requestedQuantity}, Available: {$availableStock}."
        );
    }
}
