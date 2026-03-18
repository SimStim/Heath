<?php

declare(strict_types=1);

namespace Lea\Adore;

/**
 * Imperfections. Just using a shorter term for brevity.
 *
 * Remember: Rule of Shorter Term does not apply to Germany.
 */
enum Flaw
{
    case Info;
    case Warning;
    case Severe;
    case Fatal;
}