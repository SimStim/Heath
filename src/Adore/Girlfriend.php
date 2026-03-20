<?php

declare(strict_types=1);

namespace Lea\Adore;

use BadMethodCallException;
use Exception;
use Lea\Domain\Image;
use NoDiscard;
use SebastianBergmann\Version;
use Throwable;
use Lea\Domain\Ebook;
use Lea\Domain\Text;

/**
 * You can do it because I'm your friend.
 */
final class Girlfriend
{
    private static ?self $instance = null;
    private static string $minVersion = "⊙1.0.0";
    private(set) static string $pathEbooks = REPO . "ebooks/";
    private(set) static string $pathBlocks = REPO . "blocks/";
    private(set) static string $pathFonts = REPO . "fonts/";
    private(set) static string $pathImages = REPO . "images/";
    private(set) static string $pathStyles = REPO . "styles/";
    private(set) static string $pathText = REPO . "text/";
    private(set) static string $pathEpubs = REPO . "epubs/";
    private(set) static string $pathEPUBCheck = ROOT . "/resources/EPUBCheck/";
    private(set) static string $pathPurpleRain = ROOT . "/resources/PurpleRain/";
    private(set) static array $memory = [
        "check-epub" => "no",
        "check-urls" => "no",
        "defaultcaption" => "",
        "include-images" => "yes",
        "subfolder-epub" => "",
        "subfolder-text" => "",
        "subfolder-images" => "",
    ];
    public static array $characterTransliterationMap = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'Á' => 'A', 'À' => 'A',
        'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O',
        'Õ' => 'O', 'Ö' => 'O',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'ñ' => 'n', 'Ñ' => 'N',
        'ç' => 'c', 'Ç' => 'C',
        'š' => 's', 'Š' => 'S',
        'ž' => 'z', 'Ž' => 'Z',
    ];
    private static array $characterNonGrata = [
        ' ', '.', '\'', '"', ',', ':', ';', '!', '?', '(',
        ')', '[', ']', '{', '}', '&', '/', '\\', '’', '⊙',
        '🝄', '#', '<', '>', '='
    ];
    public static array $leaPrefixes = [
        "target" => "lea-tgt-",
        "image" => "lea-img-",
        "creator" => "lea-cre-",
        "contributor" => "lea-con-",
        "font" => "lea-fnt-",
        "stylesheet" => "lea-css-",
        "collection" => "lea-col-",
        "text" => "lea-txt-",
        "ncx" => "lea-ncx-",
    ];
    private(set) string $leaVersion {
        get => $this->leaVersion ??= self::comeToMe()->computeLeaVersion(minVersion: self::$minVersion);
    }
    private(set) string $leaNameShort {
        get => $this->leaNameShort ??= "Lea";
    }
    private(set) string $leaNamePlain {
        get => $this->leaNamePlain ??= self::comeToMe()->leaNameShort . " EPUB Anvil " . self::comeToMe()->leaVersion;
    }
    private(set) string $leaName {
        get => $this->leaName ??= Fancy::BOLD . self::comeToMe()->leaNameShort . " EPUB Anvil" . Fancy::UNBOLD . " " . self::comeToMe()->leaVersion;
    }
    private(set) array $doveCries = [];
    private static Affirmation $affirmation;

    /**
     * Private constructor: you don't create Girlfriends directly!
     */
    private function __construct()
    {
        self::$affirmation = new Affirmation();
    }

    /**
     * Summary of comeToMe:
     *
     * @return Girlfriend
     */
    public static function comeToMe(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Prevent cloning and unserializing: true Girlfriend exclusivity (don't let an exception fool you)!
     */
    private function __clone(): void // should be default for final classes
    {
    }

    public function __wakeup(): void
    {
        throw new BadMethodCallException(
            message: "Cannot unserialize exclusive instance of " . self::class . ", you ... singleton?"
        );
    }

    /**
     * Only one option for now, let's keep this simple.
     *
     * @param array $argv
     * @return void
     */
    public function parseArguments(array $argv): void
    {
        self::remember(name: "check-urls", data: in_array(needle: "check-urls", haystack: $argv) ? "yes" : "no");
        self::remember(name: "check-epub", data: in_array(needle: "check-epub", haystack: $argv) ? "yes" : "no");
    }

    /**
     * Concentrate on you is all I have to do.
     *
     * Purport:
     * With a proper emotional pump, you'll gain reliable internal focus.
     * I'll do this with my one and only Girlfriend.
     *
     * Some initialization.
     *
     * @return void
     */
    public function emotionalPump(): void
    {
        libxml_use_internal_errors(use_errors: true); // suppress all libxml warnings/errors
        libxml_clear_errors(); // clear any previous
    }

    /**
     * Use git describe to compute Lea version
     *
     * @param string $minVersion
     * @return string
     */
    private function computeLeaVersion(string $minVersion): string
    {
        $version = new Version(release: $minVersion, path: ROOT)->asString();
        return str_contains(haystack: $version, needle: "-g")
            ? "$version (dev build) [PHP " . phpversion() . "]"
            : $version;
    }

    /**
     * My name is Lea and I am funky
     * My name is Lea, the one and only
     *
     * Introduce Lea to the user.
     *
     * @return void
     */
    public function myNameIsLea(): void
    {
        echo PHP_EOL
            . Fancy::PURPLE_RAIN_INVERSE_WHITE . "[ " . Girlfriend::comeToMe()->leaName . " ]" . Fancy::RESET
            . PHP_EOL . PHP_EOL;

    }

    /**
     * Hey I got trouble
     * Somebody come check around me
     *
     * @return array
     */
    function whereAmI(): array
    {
        $trace = debug_backtrace(options: DEBUG_BACKTRACE_IGNORE_ARGS, limit: 1)[0];
        return array(
            "file" => $trace['file'],
            "line" => $trace['line']
        );
    }

    /**
     * Our unique Girlfriend can remember things for us.
     * Like the subfolder.
     *
     * @param string $name
     * @param string $data
     * @return void
     */
    public function remember(string $name, string $data): void
    {
        self::$memory["$name"] = $data;
    }

    /**
     * Our unique Girlfriend can recall things for us.
     * Like the default caption string for images.
     * By allowing her to stay silent, there won't be a need to throw exceptional tantrums.
     *
     * @param string $name
     * @return string
     */
    public function recall(string $name): string
    {
        return self::$memory["$name"];
    }

    /**
     * This is what it sounds like
     * When doves cry
     *
     * When doves cry, something needs your attention.
     *
     * @param Ebook|Text $object
     * @param string $identifier
     * @param mixed ...$params
     * @return void
     * @throws Exception
     */
    public function makeDoveCry(Ebook|Text $object, string $identifier, ...$params): void
    {
        self::comeToMe()->doveCries[] = self::$affirmation->cry($object, $identifier, ...$params);
    }

    /**
     * Stop the doves from crying.
     *
     * @return void
     */
    public function silenceDoves(): void
    {
        self::comeToMe()->doveCries = [];
    }

    private static function get_calling_class(): string
    {
        $trace = debug_backtrace();
        $class = $trace[0]['class'];
        for ($i = 0; $i < count($trace); $i++) {
            if (isset($trace[$i])) // is it set?
                if ($class != $trace[$i]['class'])
                    return $trace[$i]['class'];
        }
        return "";
    }

    /**
     * Reads a file from storage into a string in memory
     * - Returns an empty string on read error
     *
     * @param string $filePath
     * @return string
     * @throws Exception
     */
    #[NoDiscard]
    public function readFile(string $filePath): string
    {
        $loadFilePath = $filePath;
        if (!file_exists($filePath)) {
            $parts = pathinfo($filePath);
            $similarFile = array_find(
                array: scandir($parts["dirname"]),
                callback: fn($file) => strcasecmp($file, $parts["basename"]) === 0
            ) ?? "";
            if ($similarFile === "") {
                Girlfriend::comeToMe()->makeDoveCry(new Text($filePath), "fileReadError",
                    $loadFilePath);
                throw new Exception("Error trying to read file '$loadFilePath'." . PHP_EOL
                    . "File requested in class '" . self::get_calling_class() . "'." . PHP_EOL);
            } else {
                $loadFilePath = $parts["dirname"] . "/" . $similarFile;
                Girlfriend::comeToMe()->makeDoveCry(new Text($filePath), "fileReadSimilar",
                    $filePath, $loadFilePath);
            }
        }
        return @file_get_contents(filename: $loadFilePath) ?: "";
    }

    /**
     * Normalizes a title string for use in OEBPS/Text.
     * "The World That Couldn't Be by Clifford D. Simak"
     * => "TheWorldThatCouldntBeByCliffordDSimak.xhtml"
     *
     * @param string $title
     * @return string
     */
    public function strToEpubTextFileName(string $title): string
    {
        return str_replace(
                search: self::$characterNonGrata,
                replace: "",
                subject: ucwords(strtr($title, self::$characterTransliterationMap))
            ) . ".xhtml";
    }

    /**
     * Normalizes a filename path string for use in OEBPS/Images.
     * "2025Q3-cover-512-QR.jpg"
     * => "2025q3-cover-512-qr.jpg"
     *
     * @param string $fileName
     * @return string
     */
    public function strToEpubImageFileName(string $fileName): string
    {
        $parts = pathinfo($fileName);
        return strtolower(
            string: str_replace(
                search: self::$characterNonGrata,
                replace: "-",
                subject: strtr(
                    trim(Girlfriend::$leaPrefixes["image"] . $parts['filename']),
                    self::$characterTransliterationMap
                )
            ) . "." . $parts['extension']
        );
    }

    /**
     * Normalizes any string for use as identifiers.
     * "The World That Couldn't Be by Clifford D. Simak.xhtml"
     * => "the-world-that-couldn-t-be-by-clifford-d--simak-xhtml"
     *
     * @param string $string
     * @return string
     */
    public function strToEpubIdentifier(string $string): string
    {
        return $string
                |> trim(...)
                |> strtolower(...)
                |> (fn($x) => strtr($x, self::$characterTransliterationMap))
                |> (fn($x) => str_replace(search: self::$characterNonGrata, replace: '', subject: $x));
    }

    /**
     * Returns a subset of an array based on a preg match of array keys.
     *
     * @param string $pattern
     * @param array $array
     * @param int $flags
     * @return array
     */
    function arrayPregKeys(string $pattern, array $array, int $flags = 0): array
    {
        return array_filter(
            array: $array,
            callback: function ($key) use ($pattern, $flags) {
                return preg_match(pattern: $pattern, subject: $key, flags: $flags);
            },
            mode: ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Extraordinary
     * The way you make me feel
     * I'm so very glad it's real
     * And not a dream
     *
     * We're taking the red pill and are returning to the real world now.
     * Right now.
     *
     * @param Throwable $throwable
     * @return never
     */
    public function extraordinary(Throwable $throwable): never
    {
        echo "Oh, I just had an oopsie..." . PHP_EOL
            . "If you'd be so kind as to help a damsel in distress," . PHP_EOL
            . "would you mind getting back to my creator and tell him:" . PHP_EOL
            . "\"" . Fancy::PURPLE_RAIN_BOLD_INVERSE_WHITE . $throwable->getMessage()
            . " in " . basename($throwable->getFile())
            . " on line " . $throwable->getLine() . Fancy::RESET . "\"" . PHP_EOL
            . "You know, I'm just a girl; I can't do simple things." . PHP_EOL;
        exit;
    }

    /**
     * Executes the final EPUB validation process using EPUBCheck.jar and returns the result.
     *
     * @param string $fileName The path to the EPUB file to be checked.
     * @return array An associative array containing the following keys:
     *               - "stdout": Output captured from the standard output of the process.
     *               - "stderr": Output captured from the standard error of the process.
     *               - "return": The exit code returned by the process.
     * @throws Exception
     */
    public function checkEpub(string $fileName): array
    {
        $cmd = "java -jar " . Girlfriend::$pathEPUBCheck . "epubcheck.jar '$fileName' --version";
        $descriptors = [
            1 => ['pipe', 'w'],     // stdout
            2 => ['pipe', 'w'],     // stderr
        ];
        $pipes = [];
        $process = proc_open($cmd, $descriptors, $pipes);
        if (!is_resource($process))
            return ['error' => 'Failed to start process.'];
        echo PHP_EOL . "[   ] Checking EPUB with EPUBCheck, please be patient.";
        $animCtr = 0;
        echo Fancy::HIDE_CURSOR;
        while (proc_get_status($process)["running"]) {
            echo "\r[ " . Fancy::PURPLE_RAIN_BOLD_INVERSE_WHITE
                . Fancy::ANIMATION[$animCtr++ % strlen(string: Fancy::ANIMATION)] . Fancy::RESET
                . " ]";
            flush();
            usleep(microseconds: 200000);
        }
        echo Fancy::UNHIDE_CURSOR . "\r" . Fancy::CLR_EOL;
        $stdout = stream_get_contents(stream: $pipes[1] ?? null);
        $stderr = stream_get_contents(stream: $pipes[2] ?? null);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $returnCode = proc_close($process);
        if ($returnCode !== 0)
            Girlfriend::comeToMe()->makeDoveCry(new Ebook(fileName: $fileName), "checkEpubFailure", $stdout, $stderr);
        return [
            "stdout" => $stdout,
            "stderr" => $stderr,
            "return" => $returnCode
        ];
    }

    /**
     * Sanitizes the stylesheets of the given eBook by modifying image URLs and ensuring their proper inclusion.
     *
     * @param Ebook $ebook The eBook instance containing the stylesheets to be processed.
     * @return array An associative array where each key is the original stylesheet's name,
     *               and each value is the sanitized content of the stylesheet.
     *               The sanitization replaces URLs of external PNG/JPEG images
     *               with normalized paths referencing the ePub's internal structure.
     * @throws Exception
     */
    #[NoDiscard]
    public function sanitizeStylesheets(Ebook $ebook): array
    {
        /**
         * The "brain-breaking" regex:
         * url\(            # literal "url("
         * \s*              # optional whitespace
         * [\'"]?           # optional quote
         * (?!data:)        # exclude data URIs
         * ([^\'")]+        # capture everything except quotes or )
         * \.(?:png|jpe?g)) # ending in png/jpg/jpeg
         * [\'"]?           # optional quote
         * \s*              # optional whitespace
         * \)
         */
        $sanitizedStylesheets = [];
        foreach ($ebook->stylesheets as $stylesheet)
            $sanitizedStylesheets[$stylesheet] =
                preg_replace_callback(
                    pattern: '/url\(\s*[\'"]?(?!data:)([^\'")]+\.(?:png|jpe?g))[\'"]?\s*\)/i',
                    callback: function ($matches) use ($ebook) {
                        $imageFileName = $matches[1];    // full path to file inside url()
                        $imageNormalizedName = Girlfriend::comeToMe()->strToEpubImageFileName(basename($imageFileName));
                        $ebook->addImages([new Image (
                            fileName: Girlfriend::$pathStyles . basename($imageFileName),
                            folder: "",
                            caption: $imageNormalizedName)
                        ]);
                        return "url('../Images/$imageNormalizedName')";
                    },
                    subject: Girlfriend::comeToMe()->readFile(filePath: Girlfriend::$pathStyles . $stylesheet)
                );
        return $sanitizedStylesheets;
    }
}
