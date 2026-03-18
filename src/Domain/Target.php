<?php

declare(strict_types=1);

namespace Lea\Domain;

/**
 * Target domain class
 */
final class Target
{
    public function __construct(
        private(set) string $name {
            set => trim(string: $value);
        },
        private(set) string $identifier {
            set => trim(string: $value);
        },
        private(set) string $targetFileName {
            set => trim(string: $value);
        }
    )
    {
    }
}
