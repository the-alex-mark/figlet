<?php

namespace ProgLib\Figlet;

use Exception;
use ProgLib\Figlet\Contracts\FontContract;
use RuntimeException;
use UnexpectedValueException;

/**
 * Представляет шрифт «Figlet».
 */
class Font implements FontContract {

    /**
     * Инициализирует новый экземпляр для работы с данными шрифта.
     *
     * @param  string $path Файл.
     * @return void
     */
    public function __construct(string $path) {
        $this->read($path);
    }

    #region Magics

    public function __debugInfo() {
        return [
            'signature' => $this->signature,
            'hardblank' => $this->hardblank,
            'height' => $this->height,
            'baseline' => $this->baseline,
            'max_length' => $this->maxLength,
            'old_layout' => $this->oldLayout,
            'comment_lines' => $this->commentLines,
            'print_direction' => $this->printDirection,
            'full_layout' => $this->fullLayout,
            'codetag_count' => $this->codetagCount
        ];
    }

    public function __toString() {
        return $this->path;
    }

    #endregion

    #region Properties

    /**
     * @var string Имя
     */
    protected string $name;

    /**
     * @var string Расположение файла
     */
    protected string $path;

    /**
     * @var string Сигнатура
     */
    protected $signature;

    /**
     * @var string ...
     */
    protected $hardblank;

    /**
     * @var int Высота символа
     */
    protected $height;

    /**
     * @var int ...
     */
    protected $baseline;

    /**
     * @var int ...
     */
    protected $maxLength;

    /**
     * @var int ...
     */
    protected $oldLayout;

    /**
     * @var int ...
     */
    protected $commentLines;

    /**
     * @var int ...
     */
    protected $printDirection;

    /**
     * @var int ...
     */
    protected $fullLayout;

    /**
     * @var int ...
     */
    protected $codetagCount;

    /**
     * @var array Список символов
     */
    protected $characters = [];

    #endregion

    #region Getters

    /**
     * Возвращает расположение файла.
     *
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Возвращает имя шрифта.
     *
     * @return string
     */
    public function getName() {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    /**
     * @inheritDoc
     */
    public function getSignature() {
        return $this->signature;
    }

    /**
     * @inheritDoc
     */
    public function getHardBlank() {
        return $this->hardblank;
    }

    /**
     * @inheritDoc
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * @inheritDoc
     */
    public function getBaseline() {
        return $this->baseline;
    }

    /**
     * @inheritDoc
     */
    public function getMaxLength() {
        return $this->maxLength;
    }

    /**
     * @inheritDoc
     */
    public function getOldLayout() {
        return $this->oldLayout;
    }

    /**
     * @inheritDoc
     */
    public function getCommentLines() {
        return $this->commentLines;
    }

    /**
     * @inheritDoc
     */
    public function getPrintDirection() {
        return $this->printDirection;
    }

    /**
     * @inheritDoc
     */
    public function getFullLayout() {
        return $this->fullLayout;
    }

    /**
     * @inheritDoc
     */
    public function getCodeTagCount() {
        return $this->codetagCount;
    }

    /**
     * @inheritDoc
     */
    public function getCharacters() {
        return $this->characters;
    }

    #endregion

    /**
     * Извлекает параметры шрифта.
     *
     * @param  resource $stream Указатель на файл.
     * @return void
     * @throws UnexpectedValueException
     */
    private function extractParameters($stream) {
        $line  = fgets($stream, 1000) ?: '';
        $count = sscanf(
            $line,
            '%5c%c %d %d %d %d %d %d %d %d',
            $this->signature,
            $this->hardblank,
            $this->height,
            $this->baseline,
            $this->maxLength,
            $this->oldLayout,
            $this->commentLines,
            $this->printDirection,
            $this->fullLayout,
            $this->codetagCount
        );

        if ($this->signature !== self::FIGLET_SIGNATURE || $count < 5)
            throw new UnexpectedValueException(sprintf('Файл [%s] не является шрифтом «Figlet».', $this->path));

        // Установка направления текста по умолчанию
        if ($count < 6)
            $this->printDirection = 0;

        // Установка корректной высоты и длины символов
        $this->height     = max(1, $this->height);
        $this->maxLength  = max(1, $this->maxLength);
        $this->maxLength += 100;
    }

    /**
     * Извлекает символы шрифта.
     *
     * @param  resource $stream Указатель на файл.
     * @return void
     */
    private function extractCharacters($stream) {
        $unicode = function ($stream) {
            if (($line = fgets($stream, 2048)) === false)
                return false;

            list($code) = explode(' ', $line);

            if (empty($code))
                return false;

            if (strpos($code, '0x') === 0)
                return hexdec(substr($code, 2));

            elseif (strpos($code, '0') === 0 && $code !== '0' || strpos($code, '-0') === 0)
                return octdec($code);

            else
                return (int)$code;
        };

        $comment = function ($stream) {
            $dummy = fgetc($stream);

            while ($dummy !== false && !feof($stream)) {
                if ($dummy === "\n") return;
                if ($dummy === "\r") {
                    $dummy = fgetc($stream);

                    if (!feof($stream) && $dummy !== "\n")
                        fseek($stream, -1, SEEK_SET);

                    return;
                }

                $dummy = fgetc($stream);
            }
        };

        $character = function ($stream) {
            $char = [];

            for ($i = 0; $i < $this->height; $i++) {
                if (feof($stream))
                    return false;

                $line = rtrim(fgets($stream, 2048), "\r\n");

                if (preg_match('#(.)\\1?$#', $line, $result) === 1)
                    $line = str_replace($result[1], '', $line);

                $char[] = $line;
            }

            return $char;
        };

        // Пропуск комментариев
        for ($line = 1; $line <= $this->commentLines; $line++)
            $comment($stream);

        // Получение символов «ASCII»
        for ($code = 32; $code < 127; $code++)
            $this->characters[$code] = $character($stream);

        // Получение немецких символов
        foreach ([ 196, 214, 220, 228, 246, 252, 223 ] as $code) {
            if (($char = $character($stream)) === false)
                return;

            if (trim(implode('', $char)) !== '')
                $this->characters[$code] = $char;
        }

        // Получение расширенных символов
        while (!feof($stream)) {
            if (($code = $unicode($stream)) === false)
                continue;

            if (($char = $character($stream)) === false)
                return;

            $this->characters[$code] = $char;
        }
    }

    /**
     * Выполняет чтение файла и запись в экземпляр всех параметров шрифта.
     *
     * @return void
     * @throws RuntimeException
     * @throws UnexpectedValueException
     */
    private function read($path) {
        $this->path = $path;

        // Проверка существования файла
        if (!file_exists($path))
            throw new RuntimeException(sprintf('Файл [%s] не найден.', $this->path));

        // Проверка поддержки «GZIP»
        if (pathinfo($path, PATHINFO_EXTENSION) == 'gz') {
            if (!function_exists('gzcompress'))
                throw new RuntimeException('Для чтения файлов сжатых архивов необходима библиотека «GZIP».');

            $path = 'compress.zlib://' . $path;
            $compressed = true;
        }
        else
            $compressed = false;

        // Чтение файла
        if (($stream = fopen($path, 'rb')) === false)
            throw new RuntimeException(sprintf('Файл [%s] не удаётся открыть.', $this->path));

        // Блокировка потока
        if (!$compressed)
            flock($stream, LOCK_SH);

        // Получение данных шрифта
        try {
            $this->extractParameters($stream);
            $this->extractCharacters($stream);
        }
        catch (Exception $e) {
            throw new RuntimeException(sprintf('При чтении файла [%s] возникла ошибка: %s', $this->path, $e->getMessage()));
        }
        finally {
            fclose($stream);
        }
    }
}
