<?php

declare(strict_types=1);

namespace Lea\Adore;

use Exception;
use Lea\Domain\Ebook;
use Lea\Domain\Text;

/**
 * I trust you're having a quick and enjoyable adjustment period
 * As you can see, we are communicating now telepathically
 */
final class Affirmation
{
    private array $messages {
        get => $this->messages ??= $this->prepareMessages();
    }

    public function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public function cry(Ebook|Text $object, string $identifier, ...$params): DoveCry
    {
        $message = $this->messages[$identifier] ?? [];
        if (empty($message))
            throw new Exception(message: "Requested error message '$identifier' is undefined.");
        if (preg_match_all(pattern: "/#\d#/", subject: $message["message"] . $message["suggestion"]) !== count($params))
            throw new Exception(
                message: "Parameter count does not match required number of placeholders for message '$identifier'"
            );
        foreach ($params as $key => $value) {
            $message["message"] = str_replace(
                search: "#" . $key + 1 . "#",
                replace: $value,
                subject: $message["message"]
            );
            $message["suggestion"] = str_replace(
                search: "#" . $key + 1 . "#",
                replace: $value,
                subject: $message["suggestion"]
            );
        }
        return new DoveCry($object, $message["flaw"], $message["message"], $message["suggestion"]);
    }

    private function prepareMessages(): array
    {
        return [
            "linkTargetUndefined" => [
                "flaw" => Flaw::Fatal,
                "message" => "Link to undefined link target.",
                "suggestion" => "Check the text content file, making sure the link target exists" . PHP_EOL
                    . "Text file name: #1#" . PHP_EOL
                    . "Link name: '#2#'"
            ],
            "urlsNotChecked" => [
                "flaw" => Flaw::Info,
                "message" => "External links were not checked this time.",
                "suggestion" => "To validate them, add 'check-urls' to your command."
                    . " [ $ " . Fancy::INVERSE . Fancy::BOLD . "lea #1# check-urls" . Fancy::RESET . " ]"
            ],
            "ebookNotWellFormed" => [
                "flaw" => Flaw::Fatal,
                "message" => "The content of the ebook config file is not well formed.",
                "suggestion" => "Check the ebook's XML config file in your XML editor of choice." . PHP_EOL
                    . "Ebook XML config file name given: #1#"
            ],
            "ebookReadError" => [
                "flaw" => Flaw::Fatal,
                "message" => "The ebook XML config file could not be read.",
                "suggestion" => "Check for typos in the file name given." . PHP_EOL
                    . "Ebook XML config file name given: #1#"
            ],
            "ebookTitleRequired" => [
                "flaw" => Flaw::Fatal,
                "message" => "The title is required.",
                "suggestion" => "Edit the ebook's XML config file, adding a single <lea:title> tag." . PHP_EOL
                    . "Ebook XML config file name given: #1#"
            ],
            "ebookMultipleTitles" => [
                "flaw" => Flaw::Fatal,
                "Multiple title tags defined in ebook." . PHP_EOL
                . "Until this is fixed, I will be using the first valid title found." . PHP_EOL
                . "Using title: '#1#'",
                "suggestion" => "Edit the ebook's XML config file, making sure to use only one <lea:title> tag." . PHP_EOL
                    . "Ebook XML config file name given: #2#"
            ],
            "ebookDescriptionRecommended" => [
                "flaw" => Flaw::Severe,
                "message" => "The description is not mandatory, but highly recommended.",
                "suggestion" => "Edit the ebook's XML config file, adding a single <lea:title> tag." . PHP_EOL
                    . "Ebook XML config file name given: #1#"
            ],
            "ebookMultipleDescriptions" => [
                "flaw" => Flaw::Severe,
                "message" => "Multiple description tags defined in ebook." . PHP_EOL
                    . "Until this is fixed, I will be using the first valid description found." . PHP_EOL
                    . "Using description: '#1#'",
                "suggestion" => "Edit the ebook's XML config file, making sure to use only one <lea:description> tag." . PHP_EOL
                    . "Ebook XML config file name given: #2#"
            ],
            "ebookPublisherMandatory" => [
                "flaw" => Flaw::Fatal,
                "message" => "The publisher data is mandatory.",
                "suggestion" => "Edit the ebook's XML config file, adding a single <lea:publisher> tag." . PHP_EOL
                    . "The contact attribute is mandatory, as well." . PHP_EOL
                    . "Ebook XML config file name given: #1#"
            ],
            "ebookRightsRecommended" => [
                "flaw" => Flaw::Severe,
                "message" => "The rights declaration is not mandatory, but highly recommended.",
                "Edit the ebook's XML config file, adding a single <lea:rights> tag." . PHP_EOL
                . "Ebook XML config file name given: #1#"
            ],
            "ebookMultipleRights" => [
                "flaw" => Flaw::Severe,
                "message" => "Multiple rights tags defined in ebook." . PHP_EOL
                    . "Until this is fixed, I will be using the first valid rights declaration." . PHP_EOL
                    . "Using rights declaration: '#1#'",
                "suggestion" => "Edit the ebook's XML config file, making sure to use only one <lea:rights> tag." . PHP_EOL
                    . "Ebook XML config file name given: #2#"
            ],
            "ebookLanguageRecommended" => [
                "flaw" => Flaw::Severe,
                "message" => "The language declaration is not mandatory, but highly recommended.",
                "suggestion" => "Edit the ebook's XML config file, ascertaining a single <lea:language> tag." . PHP_EOL
                    . "Ebook XML config file name given: #1#"
            ],
            "ebookAuthorRequired" => [
                "flaw" => Flaw::Fatal,
                "message" => "At least one author is required.",
                "suggestion" => "Edit the ebook's XML config file, adding at least one <lea:author> tag." . PHP_EOL
                    . "Ebook XML config file name given: #1#"
            ],
            "ebookInvalidAuthor" => [
                "flaw" => Flaw::Fatal,
                "message" => "Invalid author tag(s) detected in ebook." . PHP_EOL
                    . "#1# valid author definition(s) in total.",
                "suggestion" => "Edit the ebook's XML config file, checking all <lea:author> tags." . PHP_EOL
                    . "Ebook XML config file name given: #2#"
            ],
            "ebookInvalidDate" => [
                "flaw" => Flaw::Fatal,
                "message" => "The date is missing or invalid; possibly multiple date tags declared.",
                "suggestion" => "Edit the ebook's XML config file, ascertaining a single <lea:date> tag." . PHP_EOL
                    . "All of date's child tags are mandatory: <lea:created>, <lea:modified>, and <lea:issued>." . PHP_EOL
                    . "Check documentation if still unsure how to fix this." . PHP_EOL
                    . "Ebook XML config file name given: #1#"
            ],
            "ebookInvalidContributor" => [
                "flaw" => Flaw::Fatal,
                "message" => "Invalid contributor tag(s) detected in ebook." . PHP_EOL
                    . "#1# valid contributor(s) in total.",
                "suggestion" => "Check the ebook's XML config file for all <lea:contributor> tags." . PHP_EOL
                    . "Most likely cause is a an error in one of the <lea:role> tags." . PHP_EOL
                    . "Also check documentation for permitted roles." . PHP_EOL
                    . "Ebook XML config file name given: #2#"
            ],
            "ebookInvalidISBN" => [
                "flaw" => Flaw::Severe,
                "message" => "The ISBN '#1#' is invalid.",
                "suggestion" => "Edit the ebook's XML config file, checking the <lea:isbn> tag." . PHP_EOL
                    . "Ebook XML config file name given: '#2#'."
            ],
            "ebookMultipleISBNs" => [
                "flaw" => Flaw::Severe,
                "message" => "Multiple ISBN tags defined in ebook." . PHP_EOL
                    . "Until this is fixed, I will be using the first valid ISBN found." . PHP_EOL
                    . "Using ISBN: '#1#'",
                "suggestion" => "Edit the ebook's XML config file, making sure to use only one <lea:isbn> tag." . PHP_EOL
                    . "Ebook XML config file name given: #2#"
            ],
            "ebookSubjectRecommended" => [
                "flaw" => Flaw::Warning,
                "message" => "No subject declarations found in ebook. Declarations are highly recommended.",
                "suggestion" => "Edit the ebook XML config file, adding <lea:subject> tags." . PHP_EOL
                    . "Ebook XML config file name given: #1#"
            ],
            "ebookInvalidCover" => [
                "flaw" => Flaw::Fatal,
                "message" => "The cover file name was defined, but the file cannot be found in the file system." . PHP_EOL
                    . "Cover file name: #1#",
                "suggestion" => "Edit the ebook's XML config file, ascertaining the correct cover file name." . PHP_EOL
                    . "Ebook XML config file name given: #2#"
            ],
            "ebookMultipleCovers" => [
                "flaw" => Flaw::Fatal,
                "message" => "Multiple cover tags defined in ebook." . PHP_EOL
                    . "Until this is fixed, I will be using the first valid cover declaration." . PHP_EOL
                    . "Using cover file name: '#1#'",
                "suggestion" => "Edit the ebook's XML config file, making sure to use only one <lea:cover> tag." . PHP_EOL
                    . "Ebook XML config file name given: #2#"
            ],
            "textReadError" => [
                "flaw" => Flaw::Fatal,
                "message" => "The text content file could not be read.",
                "suggestion" => "Check for typos in the file name given in the ebook XML config file." . PHP_EOL
                    . "Text file name given: '#1#'."
            ],
            "textNotWellFormed" => [
                "flaw" => Flaw::Fatal,
                "message" => "The content of the text config file is not well formed.",
                "suggestion" => "Check the text file in your XML editor of choice." . PHP_EOL
                    . "Text file name given: '#1#'."
            ],
            "textTitleRequired" => [
                "flaw" => Flaw::Fatal,
                "message" => "The title is required.",
                "suggestion" => "Edit the text file, adding a single <lea:title> tag." . PHP_EOL
                    . "Text file name given: '#1#'.",
            ],
            "textMultipleTitles" => [
                "flaw" => Flaw::Severe,
                "message" => "Multiple title tags defined in text." . PHP_EOL
                    . "Until this is fixed, I will be using the first valid title found." . PHP_EOL
                    . "Using title: '#1#'",
                "suggestion" => "Edit the text file, making sure to use only one <lea:title> tag." . PHP_EOL
                    . "Text file name given: '#2#'.",
            ],
            "textAuthorRequired" => [
                "flaw" => Flaw::Fatal,
                "message" => "At least one author is required.",
                "suggestion" => "Edit the text file, adding at least one <lea:author> tag." . PHP_EOL
                    . "Text file name given: '#1#'.",
            ],
            "textInvalidAuthor" => [
                "flaw" => Flaw::Fatal,
                "message" => "Invalid author tag(s) detected in text." . PHP_EOL
                    . "#1# valid author definition(s) in total.",
                "suggestion" => "Edit the text file, checking all <lea:author> tags." . PHP_EOL
                    . "Text file name given: '#2#'.",
            ],
            "textMultipleBlurbs" => [
                "flaw" => Flaw::Severe,
                "message" => "Multiple blurbs found in text." . PHP_EOL
                    . "Continuing with the first blurb found." . PHP_EOL
                    . "Blurb I will be using: '#1#'.",
                "suggestion" => "Edit the text file, checking all <lea:blurb> tags." . PHP_EOL
                    . "Text file name given: '#2#'.",
            ],
            "imageReadError" => [
                "flaw" => Flaw::Fatal,
                "message" => "Number of image tag(s) defined in ebook, but missing in file system: #1#." . PHP_EOL
                    . "List of file name(s) declared but not found: " . PHP_EOL
                    . "#2#",
                "suggestion" => "Check the ebook's XML config file and all text files"
                    . " for missing or incorrect file names." . PHP_EOL
                    . "Ebook XML config file name given: '#3#'"
            ],
            "externalLinkCheckFailed" => [
                "flaw" => Flaw::Severe,
                "message" => "External link check failed for #1#." . PHP_EOL,
                "suggestion" => "Verify manually or ignore if intentional or temporary."
            ],
            "externalLinkCheckTimeout" => [
                "flaw" => Flaw::Warning,
                "message" => "Failed to check #1#: #2#" . PHP_EOL,
                "suggestion" => "Connection issue or timeout; check again later."
            ],
            "epubNotChecked" => [
                "flaw" => Flaw::Info,
                "message" => "EPUBCheck was not requested this time.",
                "suggestion" => "To run EPUBCheck after EPUB generation, add 'check-epub' to your command."
                    . " [ $ " . Fancy::INVERSE . Fancy::BOLD . "lea #1# check-epub" . Fancy::RESET . " ]"
            ],
            "blockReadError" => [
                "flaw" => Flaw::Fatal,
                "message" => "The block content file could not be read.",
                "suggestion" => "Check for typos in the file name, and make sure the file exists." . PHP_EOL
                    . "Block file name declared: '#1#'." . PHP_EOL
                    . "Text file name containing block declaration: '#2#'."
            ],
            "scriptUndefined" => [
                "flaw" => Flaw::Severe,
                "message" => "Requested script is not defined.",
                "suggestion" => "Check the text content file and Lea documentation to validate the script name." . PHP_EOL
                    . "Script name invoked: '#1#'" . PHP_EOL
                    . "Text file name: #2#"
            ],
            "subfolderTagUndefined" => [
                "flaw" => Flaw::Severe,
                "message" => "Requested tag '#1#' is not defined for subfolders.",
                "suggestion" => "Check the ebook configuration file to validate the subfolder tag(s)." . PHP_EOL
                    . "Ebook config file name: #2#"
            ],
            "checkEpubFailure" => [
                "flaw" => Flaw::Severe,
                "message" => "EPUBCheck returned an error code.",
                "suggestion" => "EPUBCheck summary message: " . PHP_EOL
                    . Fancy::SEVERE . "#1#" . Fancy::RESET . PHP_EOL
                    . "EPUBCheck detailed error messages: " . PHP_EOL
                    . Fancy::SEVERE . "#2#" . Fancy::RESET
            ],
            "linkedImageMissingTo" => [
                "flaw" => Flaw::Fatal,
                "message" => "Script 'linkedImage' is missing the missing the mandatory 'to' attribute.",
                "suggestion" => "Add the attribute 'to'."
            ],
            "linkedImageMissingImage" => [
                "flaw" => Flaw::Fatal,
                "message" => "Script 'linkedImage' is missing the missing the mandatory 'image' attribute.",
                "suggestion" => "Add the attribute 'image'."
            ],
            "lockedImageMissingImage" => [
                "flaw" => Flaw::Fatal,
                "message" => "Script 'lockedImage' is missing the missing the mandatory 'image' attribute.",
                "suggestion" => "Add the attribute 'image'."
            ],
            "multipleCaptions" => [
                "flaw" => Flaw::Severe,
                "message" => "Multiple captions defined in a DOM object I'm going to use the first definition.",
                "suggestion" => "Reduce the number of captions in the DOM object to 0 or 1." . PHP_EOL
                    . "DOM object filename: '#1#'." . PHP_EOL
                    . "Image name: '#2#'."
            ],
            "imageSourceUndefined" => [
                "flaw" => Flaw::Fatal,
                "message" => "Image source was not defined.",
                "suggestion" => "Check the text content file, making sure all image tags define sources." . PHP_EOL
                    . "Text file name: #1#"
            ],
            "fileReadSimilar" => [
                "flaw" => Flaw::Severe,
                "message" => "File not found, reading a similar file.",
                "suggestion" => "File names on this operating system are case-sensitive."
                    . " Check for exact spelling of the file name given." . PHP_EOL
                    . "File name provided: " . Fancy::INVERSE . Fancy::BOLD . "#1#" . Fancy::RESET . "." . PHP_EOL
                    . "File name read:     " . Fancy::INVERSE . Fancy::BOLD . "#2#" . Fancy::RESET . "." . PHP_EOL
                    . "This is a low-level error message, therefore I cannot be more specific"
                    . " about the exact location."
            ],
            "fileReadError" => [
                "flaw" => Flaw::Fatal,
                "message" => "Low-level error while reading file.",
                "suggestion" => "File requested: #1#."
            ],
        ];
    }
}
