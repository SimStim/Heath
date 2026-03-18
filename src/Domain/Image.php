<?php

declare(strict_types=1);

namespace Lea\Domain;

/**
 * Image domain class
 */
final class Image
{
    public function __construct(
        private(set) string $fileName {
            set => trim(string: $value);
        },
        private(set) string $folder {
            set => trim(string: $value);
        },
        private(set) string $caption = "" {
            set => trim(string: $value);
        }
    )
    {
    }
}
