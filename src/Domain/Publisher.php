<?php

declare(strict_types=1);

namespace Lea\Domain;

class Publisher
{
    public function __construct(
        private(set) string $imprint = "" {
            set => trim(string: $value);
        },
        private(set) string $contact = "" {
            set => trim(string: $value);
        },
    )
    {
    }

    /**
     * Checks if publisher data has been extracted correctly
     *
     * Return values:
     * - true on valid dates on file.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return ($this->imprint !== "" && $this->contact !== "");
    }
}