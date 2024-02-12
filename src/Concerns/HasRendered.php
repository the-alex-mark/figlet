<?php

namespace ProgLib\Figlet\Concerns;

use ProgLib\Figlet\Contracts\FigletContract;
use UnexpectedValueException;

/**
 * За основу взят исходный код проекта **zend-text**.
 *
 * @see       https://github.com/zendframework/zend-text
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 */
trait HasRendered {

    #region Properties

    /**
     * @var int Максимальная ширина текста
     */
    protected int $width = 200;

    /**
     * @var int Режим сглаживания
     */
    protected int $smushMode = 0;

    /**
     * @var int Override font file smush layout
     */
    protected int $smushOverride = 0;

    /**
     * @var int Режим сглаживания, определённый настройками шрифта
     */
    protected int $fontSmush = 0;

    /**
     * @var int Пользовательский режим сглаживания
     */
    protected $userSmush = 0;

    /**
     * @var bool Обработка абзацев
     */
    protected $handleParagraphs = false;

    /**
     * @var int Режим выравнивания текста
     */
    protected $justification = null;

    /**
     * @var bool Режим направления текста
     */
    protected $backward = null;

    /**
     * @var int Расстояние между символами
     */
    protected $stretching = 0;

    /**
     * Previous character width
     *
     * @var int
     */
    protected $previousCharWidth = 0;

    /**
     * Current character width
     *
     * @var int
     */
    protected $currentCharWidth = 0;

    /**
     * Current outline length
     *
     * @var int
     */
    protected $outlineLength = 0;

    /**
     * Maximum outline length
     *
     * @var int
     */
    protected $outlineLengthLimit = 0;

    /**
     * In character line
     *
     * @var string
     */
    protected $inCharLine;

    /**
     * In character line length
     *
     * @var int
     */
    protected $inCharLineLength = 0;

    /**
     * Maximum in character line length
     *
     * @var int
     */
    protected $inCharLineLengthLimit = 0;

    /**
     * Current char
     *
     * @var array
     */
    protected $currentChar = null;

    /**
     * Current output line
     *
     * @var array
     */
    protected $outputLine;

    /**
     * Current output
     *
     * @var string
     */
    protected $output;

    #endregion

    #region Styles

    /**
     * @inheritDoc
     */
    public function setWidth(int $width) {
        $this->width = max(1, $width);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSmushing(int $mode) {
        if ($mode < -1)
            $this->smushOverride = self::SMO_NO;

        else {
            if ($mode === 0)
                $this->userSmush = FigletContract::SM_KERN;

            elseif ($mode === -1)
                $this->userSmush = 0;

            else
                $this->userSmush = (($mode & 63) | FigletContract::SM_SMUSH);

            $this->smushOverride = self::SMO_YES;
        }

        $this->_setUsedSmush();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setHandleParagraphs(bool $bool) {
        $this->handleParagraphs = $bool;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setJustification(int $justification) {
        $this->justification = $justification;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setBackward(bool $bool) {
        $this->backward = $bool;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setStretching(int $stretching) {
        $this->stretching = max(0, $stretching);

        return $this;
    }

    #endregion

    #region Helpers

    /**
     * Задаёт первоначальные параметры визуализации, исходя из настроек шрифта.
     *
     * @return void
     */
    protected function _setParameters() {

        // Установка режима сглаживания
        if (empty($this->font->getFullLayout())) {
            if ($this->font->getOldLayout() === 2)
                $this->fontSmush = FigletContract::SM_KERN;

            elseif ($this->font->getOldLayout() < 0)
                $this->fontSmush = 0;

            else
                $this->fontSmush = (($this->font->getOldLayout() & 31) | FigletContract::SM_SMUSH);
        }
        else
            $this->fontSmush = $this->font->getFullLayout();

        // Установка необходимости переопределения режима сглаживания
        $this->_setUsedSmush();

        // Установка режима направления
        if (is_null($this->backward))
            $this->backward = (bool)$this->font->getPrintDirection();

        // Установка режима выравнивания
        if (is_null($this->justification))
            $this->justification = (2 * $this->font->getPrintDirection());
    }

    /**
     * Set the used smush mode, according to smush override, user smush and
     * font smush.
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _setUsedSmush()
    {
        // @codingStandardsIgnoreEnd
        if ($this->smushOverride === self::SMO_NO) {
            $this->smushMode = $this->fontSmush;
        } elseif ($this->smushOverride === self::SMO_YES) {
            $this->smushMode = $this->userSmush;
        } elseif ($this->smushOverride === self::SMO_FORCE) {
            $this->smushMode = ($this->fontSmush | $this->userSmush);
        }
    }

    /**
     * Unicode compatible ord() method
     *
     * @param  string $c
     * @return int The char to get the value from
     */
    // @codingStandardsIgnoreStart
    protected function _uniOrd($c)
    {
        // @codingStandardsIgnoreEnd
        $h = ord($c[0]);

        if ($h <= 0x7F) {
            $ord = $h;
        } elseif ($h < 0xC2) {
            $ord = 0;
        } elseif ($h <= 0xDF) {
            $ord = (($h & 0x1F) << 6 | (ord($c[1]) & 0x3F));
        } elseif ($h <= 0xEF) {
            $ord = (($h & 0x0F) << 12 | (ord($c[1]) & 0x3F) << 6 | (ord($c[2]) & 0x3F));
        } elseif ($h <= 0xF4) {
            $ord = (($h & 0x0F) << 18 | (ord($c[1]) & 0x3F) << 12 |
                (ord($c[2]) & 0x3F) << 6 | (ord($c[3]) & 0x3F));
        } else {
            $ord = 0;
        }

        return $ord;
    }

    /**
     * Puts the given string, substituting blanks for hardblanks. If outputWidth
     * is 1, puts the entire string; otherwise puts at most outputWidth - 1
     * characters. Puts a newline at the end of the string. The string is left-
     * justified, centered or right-justified (taking outputWidth as the screen
     * width) if justification is 0, 1 or 2 respectively.
     *
     * @param  string $string The string to add to the output
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _putString($string)
    {
        // @codingStandardsIgnoreEnd
        $length = strlen($string);

        if ($this->width > 1) {
            if ($length > ($this->width - 1)) {
                $length = ($this->width - 1);
            }

            if ($this->justification > 0) {
                for ($i = 1;
                     ((3 - $this->justification) * $i + $length + $this->justification - 2) < $this->width;
                     $i++) {
                    $this->output .= ' ';
                }
            }
        }

        $this->output .= str_replace($this->font->getHardBlank(), ' ', $string) . "\n";
    }

    /**
     * Appends the current line to the output
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _appendLine()
    {
        // @codingStandardsIgnoreEnd
        for ($i = 0; $i < $this->font->getHeight(); $i++) {
            $this->_putString($this->outputLine[$i]);
        }

        $this->_clearLine();
    }

    /**
     * Splits inCharLine at the last word break (bunch of consecutive blanks).
     * Makes a new line out of the first part and appends it using appendLine().
     * Makes a new line out of the second part and returns.
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _splitLine()
    {
        // @codingStandardsIgnoreEnd
        $gotSpace = false;
        for ($i = ($this->inCharLineLength - 1); $i >= 0; $i--) {
            if (! $gotSpace && $this->inCharLine[$i] === ' ') {
                $gotSpace  = true;
                $lastSpace = $i;
            }

            if ($gotSpace && $this->inCharLine[$i] !== ' ') {
                break;
            }
        }

        $firstLength = ($i + 1);
        $lastLength  = ($this->inCharLineLength - $lastSpace - 1);

        $firstPart = '';
        for ($i = 0; $i < $firstLength; $i++) {
            $firstPart[$i] = $this->inCharLine[$i];
        }

        $lastPart = '';
        for ($i = 0; $i < $lastLength; $i++) {
            $lastPart[$i] = $this->inCharLine[($lastSpace + 1 + $i)];
        }

        $this->_clearLine();

        for ($i = 0; $i < $firstLength; $i++) {
            $this->_addChar($firstPart[$i]);
        }

        $this->_appendLine();

        for ($i = 0; $i < $lastLength; $i++) {
            $this->_addChar($lastPart[$i]);
        }
    }

    /**
     * Clears the current line
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _clearLine()
    {
        // @codingStandardsIgnoreEnd
        for ($i = 0; $i < $this->font->getHeight(); $i++) {
            $this->outputLine[$i] = '';
        }

        $this->outlineLength    = 0;
        $this->inCharLineLength = 0;
    }

    protected function _addStretching() {
        if ($this->stretching > 0 && $this->outlineLength > 0)
            return str_repeat(' ', $this->stretching);

        return '';
    }

    /**
     * Attempts to add the given character onto the end of the current line.
     * Returns true if this can be done, false otherwise.
     *
     * @param  string $char Character which to add to the output
     * @return bool
     */
    // @codingStandardsIgnoreStart
    protected function _addChar($char)
    {
        // @codingStandardsIgnoreEnd
        $this->_getLetter($char);

        if ($this->currentChar === null) {
            return true;
        }

        $smushAmount = $this->_smushAmount();

        if (($this->outlineLength + $this->currentCharWidth - $smushAmount) > $this->outlineLengthLimit
            || ($this->inCharLineLength + 1) > $this->inCharLineLengthLimit) {
            return false;
        }

        for ($row = 0; $row < $this->font->getHeight(); $row++) {
            if ($this->backward === true) {
                $tempLine = $this->currentChar[$row];

                for ($k = 0; $k < $smushAmount; $k++) {
                    $position            = ($this->currentCharWidth - $smushAmount + $k);
                    $tempLine[$position] = $this->_smushem($tempLine[$position], $this->outputLine[$row][$k]);
                }

                $this->outputLine[$row] = $tempLine . $this->_addStretching() . substr($this->outputLine[$row], $smushAmount);
            } else {
                for ($k = 0; $k < $smushAmount; $k++) {
                    if (($this->outlineLength - $smushAmount + $k) < 0) {
                        continue;
                    }

                    $position = ($this->outlineLength - $smushAmount + $k);
                    $leftChar = $this->outputLine[$row][$position] ?? null;

                    $this->outputLine[$row][$position] = $this->_smushem($leftChar, $this->currentChar[$row][$k]);
                }

                $this->outputLine[$row] .= $this->_addStretching() . substr($this->currentChar[$row], $smushAmount);
            }
        }

        $this->outlineLength                         = strlen($this->outputLine[0]);
        $this->inCharLine[$this->inCharLineLength++] = $char;

        return true;
    }

    /**
     * Gets the requested character and sets current and previous char width.
     *
     * @param  string $char The character from which to get the letter of
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _getLetter($char)
    {
        // @codingStandardsIgnoreEnd
        if (array_key_exists($this->_uniOrd($char), $this->font->getCharacters())) {
            $this->currentChar       = $this->font->getCharacters()[$this->_uniOrd($char)];
            $this->previousCharWidth = $this->currentCharWidth;
            $this->currentCharWidth  = strlen($this->currentChar[0]);
        } else {
            $this->currentChar = null;
        }
    }

    /**
     * Returns the maximum amount that the current character can be smushed into
     * the current line.
     *
     * @return int
     */
    // @codingStandardsIgnoreStart
    protected function _smushAmount()
    {
        // @codingStandardsIgnoreEnd
        if (($this->smushMode & (self::SM_SMUSH | self::SM_KERN)) === 0) {
            return 0;
        }

        if ($this->stretching > 0)
            return 0;

        $maxSmush = $this->currentCharWidth;

        for ($row = 0; $row < $this->font->getHeight(); $row++) {
            if ($this->backward === true) {
                $charbd = strlen($this->currentChar[$row]);
                while (true) {
                    if (! isset($this->currentChar[$row][$charbd])) {
                        $leftChar = null;
                    } else {
                        $leftChar = $this->currentChar[$row][$charbd];
                    }

                    if ($charbd > 0 && ($leftChar === null || $leftChar == ' ')) {
                        $charbd--;
                    } else {
                        break;
                    }
                }

                $linebd = 0;
                while (true) {
                    if (! isset($this->outputLine[$row][$linebd])) {
                        $rightChar = null;
                    } else {
                        $rightChar = $this->outputLine[$row][$linebd];
                    }

                    if ($rightChar === ' ') {
                        $linebd++;
                    } else {
                        break;
                    }
                }

                $amount = ($linebd + $this->currentCharWidth - 1 - $charbd);
            } else {
                $linebd = strlen($this->outputLine[$row]);
                while (true) {
                    if (! isset($this->outputLine[$row][$linebd])) {
                        $leftChar = null;
                    } else {
                        $leftChar = $this->outputLine[$row][$linebd];
                    }

                    if ($linebd > 0 && ($leftChar === null || $leftChar == ' ')) {
                        $linebd--;
                    } else {
                        break;
                    }
                }

                $charbd = 0;
                while (true) {
                    if (! isset($this->currentChar[$row][$charbd])) {
                        $rightChar = null;
                    } else {
                        $rightChar = $this->currentChar[$row][$charbd];
                    }

                    if ($rightChar === ' ') {
                        $charbd++;
                    } else {
                        break;
                    }
                }

                $amount = ($charbd + $this->outlineLength - 1 - $linebd);
            }

            if (empty($leftChar) || $leftChar === ' ') {
                $amount++;
            } elseif (! empty($rightChar)) {
                if ($this->_smushem($leftChar, $rightChar) !== null) {
                    $amount++;
                }
            }

            $maxSmush = min($amount, $maxSmush);
        }

        return $maxSmush;
    }

    /**
     * Given two characters, attempts to smush them into one, according to the
     * current smushmode. Returns smushed character or false if no smushing can
     * be done.
     *
     * Smushmode values are sum of following (all values smush blanks):
     *
     *  1: Smush equal chars (not hardblanks)
     *  2: Smush '_' with any char in hierarchy below
     *  4: hierarchy: "|", "/\", "[]", "{}", "()", "<>"
     *     Each class in hier. can be replaced by later class.
     *  8: [ + ] -> |, { + } -> |, ( + ) -> |
     * 16: / + \ -> X, > + < -> X (only in that order)
     * 32: hardblank + hardblank -> hardblank
     *
     * @param  string $leftChar  Left character to smush
     * @param  string $rightChar Right character to smush
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected function _smushem($leftChar, $rightChar)
    {
        // @codingStandardsIgnoreEnd
        if ($leftChar === ' ') {
            return $rightChar;
        }

        if ($rightChar === ' ') {
            return $leftChar;
        }

        if ($this->previousCharWidth < 2 || $this->currentCharWidth < 2) {
            // Disallows overlapping if the previous character or the current
            // character has a width of one or zero.
            return;
        }

        if (($this->smushMode & self::SM_SMUSH) === 0) {
            // Kerning
            return;
        }

        if ($this->stretching > 0)
            return;

        if (($this->smushMode & 63) === 0) {
            // This is smushing by universal overlapping
            if ($leftChar === ' ') {
                return $rightChar;
            } elseif ($rightChar === ' ') {
                return $leftChar;
            } elseif ($leftChar === $this->font->getHardBlank()) {
                return $rightChar;
            } elseif ($rightChar === $this->font->getHardBlank()) {
                return $rightChar;
            } elseif ($this->backward === true) {
                return $leftChar;
            } else {
                // Occurs in the absence of above exceptions
                return $rightChar;
            }
        }

        if (($this->smushMode & self::SM_HARDBLANK) > 0) {
            if ($leftChar === $this->font->getHardBlank() && $rightChar === $this->font->getHardBlank()) {
                return $leftChar;
            }
        }

        if ($leftChar === $this->font->getHardBlank() && $rightChar === $this->font->getHardBlank()) {
            return;
        }

        if (($this->smushMode & self::SM_EQUAL) > 0) {
            if ($leftChar === $rightChar) {
                return $leftChar;
            }
        }

        if (($this->smushMode & self::SM_LOWLINE) > 0) {
            if ($leftChar === '_' && strchr('|/\\[]{}()<>', $rightChar) !== false) {
                return $rightChar;
            } elseif ($rightChar === '_' && strchr('|/\\[]{}()<>', $leftChar) !== false) {
                return $leftChar;
            }
        }

        if (($this->smushMode & self::SM_HIERARCHY) > 0) {
            if ($leftChar === '|' && strchr('/\\[]{}()<>', $rightChar) !== false) {
                return $rightChar;
            } elseif ($rightChar === '|' && strchr('/\\[]{}()<>', $leftChar) !== false) {
                return $leftChar;
            } elseif (strchr('/\\', $leftChar) && strchr('[]{}()<>', $rightChar) !== false) {
                return $rightChar;
            } elseif (strchr('/\\', $rightChar) && strchr('[]{}()<>', $leftChar) !== false) {
                return $leftChar;
            } elseif (strchr('[]', $leftChar) && strchr('{}()<>', $rightChar) !== false) {
                return $rightChar;
            } elseif (strchr('[]', $rightChar) && strchr('{}()<>', $leftChar) !== false) {
                return $leftChar;
            } elseif (strchr('{}', $leftChar) && strchr('()<>', $rightChar) !== false) {
                return $rightChar;
            } elseif (strchr('{}', $rightChar) && strchr('()<>', $leftChar) !== false) {
                return $leftChar;
            } elseif (strchr('()', $leftChar) && strchr('<>', $rightChar) !== false) {
                return $rightChar;
            } elseif (strchr('()', $rightChar) && strchr('<>', $leftChar) !== false) {
                return $leftChar;
            }
        }

        if (($this->smushMode & self::SM_PAIR) > 0) {
            if ($leftChar === '[' && $rightChar === ']') {
                return '|';
            } elseif ($rightChar === '[' && $leftChar === ']') {
                return '|';
            } elseif ($leftChar === '{' && $rightChar === '}') {
                return '|';
            } elseif ($rightChar === '{' && $leftChar === '}') {
                return '|';
            } elseif ($leftChar === '(' && $rightChar === ')') {
                return '|';
            } elseif ($rightChar === '(' && $leftChar === ')') {
                return '|';
            }
        }

        if (($this->smushMode & self::SM_BIGX) > 0) {
            if ($leftChar === '/' && $rightChar === '\\') {
                return '|';
            } elseif ($rightChar === '/' && $leftChar === '\\') {
                return 'Y';
            } elseif ($leftChar === '>' && $rightChar === '<') {
                return 'X';
            }
        }

        return;
    }

    #endregion

    /**
     * Возвращает визуализованный текст.
     *
     * @param  string $text Текст.
     * @param  string $encoding Кодировка. По умолчанию «UTF-8»
     * @return string
     */
    protected function generate(string $text, string $encoding = 'utf-8') {
        $this->_setParameters();

        // Преобразование в кодировку «UTF-8»
        $text = mb_convert_encoding($text, 'utf-8', $encoding);

        // Проверка корректности преобразования
        if ($text === false)
            throw new UnexpectedValueException(sprintf('Не удалось конвертировать текст в кодировку «%s»', strtoupper($encoding)));

        $this->output     = '';
        $this->outputLine = [];

        $this->_clearLine();

        $this->outlineLengthLimit    = ($this->width - 1);
        $this->inCharLineLengthLimit = ($this->width * 4 + 100);

        $wordBreakMode  = 0;
        $lastCharWasEol = false;
        $textLength     = mb_strlen($text);

        for ($charNum = 0; $charNum < $textLength; $charNum++) {
            // Handle paragraphs
            $char = mb_substr($text, $charNum, 1);

            if ($char === "\n" && $this->handleParagraphs && ! $lastCharWasEol) {
                $nextChar = mb_substr($text, ($charNum + 1), 1);
                if (! $nextChar) {
                    $nextChar = null;
                }

                $char = (ctype_space($nextChar)) ? "\n" : ' ';
            }

            $lastCharWasEol = (ctype_space($char) && $char !== "\t" && $char !== ' ');

            if (ctype_space($char)) {
                $char = ($char === "\t" || $char === ' ') ? ' ' : "\n";
            }

            // Skip unprintable characters
            $ordChar = $this->_uniOrd($char);
            if (($ordChar > 0 && $ordChar < 32 && $char !== "\n") || $ordChar === 127) {
                continue;
            }

            // Build the character
            // Note: The following code is complex and thoroughly tested.
            // Be careful when modifying!
            do {
                $charNotAdded = false;

                if ($wordBreakMode === -1) {
                    if ($char === ' ') {
                        break;
                    } elseif ($char === "\n") {
                        $wordBreakMode = 0;
                        break;
                    }

                    $wordBreakMode = 0;
                }

                if ($char === "\n") {
                    $this->_appendLine();
                    $wordBreakMode = false;
                } elseif ($this->_addChar($char)) {
                    if ($char !== ' ') {
                        $wordBreakMode = ($wordBreakMode >= 2) ? 3 : 1;
                    } else {
                        $wordBreakMode = ($wordBreakMode > 0) ? 2 : 0;
                    }
                } elseif ($this->outlineLength === 0) {
                    for ($i = 0; $i < $this->font->getHeight(); $i++) {
                        if ($this->backward === true && $this->width > 1) {
                            $offset = (strlen($this->currentChar[$i]) - $this->outlineLengthLimit);
                            $this->_putString(substr($this->currentChar[$i], $offset));
                        } else {
                            $this->_putString($this->currentChar[$i]);
                        }
                    }

                    $wordBreakMode = -1;
                } elseif ($char === ' ') {
                    if ($wordBreakMode === 2) {
                        $this->_splitLine();
                    } else {
                        $this->_appendLine();
                    }

                    $wordBreakMode = -1;
                } else {
                    if ($wordBreakMode >= 2) {
                        $this->_splitLine();
                    } else {
                        $this->_appendLine();
                    }

                    $wordBreakMode = ($wordBreakMode === 3) ? 1 : 0;
                    $charNotAdded  = true;
                }
            } while ($charNotAdded);
        }

        if ($this->outlineLength !== 0) {
            $this->_appendLine();
        }

        return $this->output;
    }
}
