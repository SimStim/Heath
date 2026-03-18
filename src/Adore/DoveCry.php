<?php

declare(strict_types=1);

namespace Lea\Adore;

/**
 * DoveCry
 *
 * How can you just leave me standing
 * Alone in a world that's so cold? (So cold)
 */
final readonly class DoveCry
{
    /**
     * Store a message.
     *
     * One Class to rule them all, one Class to find them,
     * One Class to bring them all, you get the idea.
     *
     * @param object $domainObject
     * @param Flaw $flaw
     * @param string $message
     * @param string $suggestion
     */
    public function __construct(
        private(set) object $domainObject,
        private(set) Flaw   $flaw = Flaw::Fatal,
        private(set) string $message = "[We gaze into that void, hearts bared to its depths, and in our longing stare ... something shifts.]",
        private(set) string $suggestion = ""
    )
    {
    }
}