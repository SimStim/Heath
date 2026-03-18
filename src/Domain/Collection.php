<?php

declare(strict_types=1);

namespace Lea\Domain;

class Collection
{
    public function __construct(
        private(set) string $title = "" {
            set => trim(string: $value);
        },
        private(set) string $type = "" {
            set => trim(string: $value);
        },
        private(set) string $position = "" {
            set => trim(string: $value);
        },
        private(set) string $issn = "" {
            set => trim(string: $value);
        },
    )
    {
    }

    /**
     * Checks if collection data has been extracted correctly
     *
     * Return values:
     * - true on valid dates on file.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return ($this->title !== "" && $this->type !== "" && $this->position !== "" && $this->issn !== "");
    }
}
