<?php

namespace ProgLib\Figlet;

use ProgLib\Figlet\Contracts\FigletContract;
use ProgLib\Figlet\Contracts\FontContract;

/**
 * Представление текста в виде рисунка **ASCII**.
 */
class Figlet implements FigletContract {

    use Concerns\HasRendered;

    /**
     * Инициализирует новый экземпляр для стилизации текста.
     *
     * @param  string $path Расположение хранилища шрифтов.
     * @return void
     */
    public function __construct($path = null) {
        $this
            ->setStorage($path ?: realpath(__DIR__ . '/../resources/fonts'))
            ->setFont('slant');
    }

    #region Constants

    /**
     * Поддерживаемый формат файла
     */
    private const FIGLET_FORMAT = 'flf';

    /**
     * Smush mode override modes
     */
    private const SMO_NO    = 0;
    private const SMO_YES   = 1;
    private const SMO_FORCE = 2;

    #endregion

    #region Properties

    /**
     * @var string Хранилище шрифтов
     */
    protected string $storage;

    /**
     * @var FontContract Шрифт
     */
    protected FontContract $font;

    #endregion

    #region Settings

    /**
     * @inheritDoc
     */
    public function setStorage($path) {
        $this->storage = $path;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setFont($name) {
        $this->font = new Font($this->storage . DIRECTORY_SEPARATOR . $name . '.' . self::FIGLET_FORMAT);

        return $this;
    }

    #endregion

    /**
     * @inheritDoc
     */
    public function render(string $text, string $encoding = 'utf-8') {
        return $this->generate($text);
    }

    /**
     * @inheritDoc
     */
    public function write(string $text, string $encoding = 'utf-8') {
        echo $this->render($text);
    }
}
