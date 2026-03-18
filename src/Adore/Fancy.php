<?php

declare(strict_types=1);

namespace Lea\Adore;

/**
 * Fancy class for fancy ANSI
 *
 * Have you found yourself in love before
 * Tell me ain't it a different kind of thang
 */
final readonly class Fancy
{
    public const string STRIP_ANSI_REGEX = "/\\e[[][A-Za-z0-9];?[ 0-9]*m?/";
    /**
     * Let's start climactically
     */
    public const string ANIMATION = "-\|/";
    public const string HIDE_CURSOR = "\033[?25l";
    public const string UNHIDE_CURSOR = "\033[?25h";
    public const string CLR_EOL = "\033[K";

    /**
     * Basic foreground colors
     */
    public const string BLACK = "\033[30m";
    public const string RED = "\033[31m";
    public const string GREEN = "\033[32m";
    public const string YELLOW = "\033[33m";
    public const string BLUE = "\033[34m";
    public const string MAGENTA = "\033[35m";
    public const string CYAN = "\033[36m";
    public const string WHITE = "\033[37m";

    /**
     * Bright/bold variants
     */
    public const string BRIGHT_BLACK = "\033[90m";
    public const string BRIGHT_RED = "\033[91m";
    public const string BRIGHT_GREEN = "\033[92m";
    public const string BRIGHT_YELLOW = "\033[93m";
    public const string BRIGHT_BLUE = "\033[94m";
    public const string BRIGHT_MAGENTA = "\033[95m";
    public const string BRIGHT_CYAN = "\033[96m";
    public const string BRIGHT_WHITE = "\033[97m";

    /**
     * Background colors
     */
    public const string BG_BLACK = "\033[40m";
    public const string BG_RED = "\033[41m";
    public const string BG_GREEN = "\033[42m";
    public const string BG_YELLOW = "\033[43m";
    public const string BG_BLUE = "\033[44m";
    public const string BG_MAGENTA = "\033[45m";
    public const string BG_CYAN = "\033[46m";
    public const string BG_WHITE = "\033[47m";

    /**
     * Effects/styles
     */
    public const string RESET = "\033[0m";
    public const string BOLD = "\033[1m";
    public const string UNBOLD = "\033[22m";
    public const string DIM = "\033[2m";
    public const string ITALIC = "\033[3m";
    public const string UNDERLINE = "\033[4m";
    public const string BLINK = "\033[5m";
    public const string UNBLINK = "\033[25m";
    public const string FAST_BLINK = "\033[6m"; // not widely supported
    public const string INVERSE = "\033[7m";
    public const string HIDDEN = "\033[8m";
    public const string STRIKETHRU = "\033[9m";

    /**
     * Precomposed styles.
     * Lea's personality palette.
     */
    public const string INFO = self::CYAN;
    public const string SUCCESS = self::GREEN . self::BOLD;
    public const string WARNING = self::BRIGHT_YELLOW;
    public const string SEVERE = self::RED;
    public const string FATAL = self::RED . self::INVERSE . self::BOLD . self::BLINK;
    public const string SUGGESTION = self::WHITE . self::BOLD;
    public const string NOTICE = self::MAGENTA;
    public const string DEBUG = self::BRIGHT_BLACK;
    public const string PURPLE_RAIN = "\033[38;2;144;99;205m";
    public const string PURPLE_RAIN_INVERSE_WHITE = self::BG_WHITE . self::PURPLE_RAIN . self::INVERSE;
    public const string PURPLE_RAIN_BOLD = self::PURPLE_RAIN . self::BOLD;
    public const string PURPLE_RAIN_BOLD_INVERSE_WHITE = self::BG_WHITE . self::PURPLE_RAIN_BOLD . self::INVERSE;

    /**
     * Helpers for cleaner usage
     */
    public static function info(string $msg): string
    {
        return self::INFO . $msg . self::RESET;
    }

    public static function success(string $msg): string
    {
        return self::SUCCESS . $msg . self::RESET;
    }

    public static function warning(string $msg): string
    {
        return self::WARNING . $msg . self::RESET;
    }

    public static function severe(string $msg): string
    {
        return self::SEVERE . $msg . self::RESET;
    }

    public static function fatal(string $msg): string
    {
        return self::FATAL . $msg . self::RESET;
    }

    public static function suggestion(string $msg): string
    {
        return self::SUGGESTION . $msg . self::RESET;
    }
}