<?php

declare(strict_types=1);

namespace Lea\Domain;

/**
 * Author domain class
 */
final class Author
{
    public function __construct(
        private(set) string $name {
            set => trim(string: $value);
        },
        private(set) string $fileAs = "" {
            set => trim(string: $value);
        }
    )
    {
    }
}
