<?php

declare(strict_types=1);

namespace Lea\Domain;

/**
 * Author domain class
 */
final class Contributor
{
    private(set) static array $permittedRoles = [
        "edt", "trl", "bkp", "bkd", "tyg", "mrk", "pfr", "cov", "ill", "art",
        "blw"
    ];

    public function __construct(
        private(set) string         $name {
            set => trim(string: $value);
        },
        private(set) readonly array $roles = []
    )
    {
    }
}
