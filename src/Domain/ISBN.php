<?php

declare(strict_types=1);

namespace Lea\Domain;

use NoDiscard;

/**
 * ISBN domain class
 */
final class ISBN
{
    private(set) bool $isValid {
        get => $this->isValid ??= $this->calculateIsValid();
    }

    public function __construct(
        private(set) string $isbn {
            set => trim(str_replace(search: ["-", " "], replace: "", subject: $value));
        }
    )
    {
    }

    /**
     * Checks a passed string for conformity with ISBN-13 specs
     *
     * @return bool
     */
    #[NoDiscard]
    public function calculateIsValid(): bool
    {
        if (!preg_match(pattern: '/^\d{13}$/', subject: $this->isbn))
            return false;
        $sum = 0;
        for ($i = 0; $i < 12; $i++)
            $sum += ($i % 2 === 0)
                ? (int)$this->isbn[$i]
                : (int)$this->isbn[$i] * 3;
        return ((10 - ($sum % 10)) % 10 === (int)$this->isbn[12]);
    }
}
