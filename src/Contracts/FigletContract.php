<?php

namespace ProgLib\Figlet\Contracts;

interface FigletContract {

    #region Constants

    /**
     * Режим сглаживания
     */
    const SM_EQUAL = 0x01;

    /**
     * Режим сглаживания
     */
    const SM_LOWLINE = 0x02;

    /**
     * Режим сглаживания
     */
    const SM_HIERARCHY = 0x04;

    /**
     * Режим сглаживания
     */
    const SM_PAIR = 0x08;

    /**
     * Режим сглаживания
     */
    const SM_BIGX = 0x10;

    /**
     * Режим сглаживания
     */
    const SM_HARDBLANK = 0x20;

    /**
     * Режим сглаживания
     */
    const SM_KERN = 0x40;

    /**
     * Режим сглаживания
     */
    const SM_SMUSH = 0x80;

    /**
     * Выравнивание по левому краю
     */
    const JUSTIFICATION_LEFT = 0;

    /**
     * Выравнивание по центру
     */
    const JUSTIFICATION_CENTER = 1;

    /**
     * Выравнивание по правому краю
     */
    const JUSTIFICATION_RIGHT = 2;

    #endregion

    #region Settings

    /**
     * Задаёт хранилище шрифтов.
     *
     * @param  string $path Расположение хранилища шрифтов.
     * @return self
     */
    public function setStorage(string $path);

    /**
     * Задаёт шрифт.
     *
     * @param  string $name Имя шрифта.
     * @return self
     */
    public function setFont(string $name);

    #endregion

    #region Styles

    /**
     * Задаёт максимальную ширину выходной строки.
     *
     * Используется для переноса слов, а также для выравнивания.
     *
     * @param  int $width Ширина.
     * @return self
     */
    public function setWidth(int $width);

    /**
     * Задаёт режим горизонтальной склейки текста.
     *
     * По умолчанию используется настройка файла шрифта.
     *
     * @param  int $mode Целочисленное битовое поле, которое определяет, как отдельные символы объединяются вместе.
     *                   Допустимые значения: {@see SM_EQUAL}, {@see SM_LOWLINE SM_LOWLINE}, {@see SM_HIERARCHY}, {@see SM_PAIR}, {@see SM_BIGX}, {@see SM_HARDBLANK}, {@see SM_KERN}, {@see SM_SMUSH}.
     * @return self
     */
    public function setSmushing(int $mode);

    /**
     * Задаёт обработку абзацев.
     *
     * @param  bool $bool Логическое значение, указывающее, как обрабатываются новые строки. По умолчанию отключено.
     * @return self
     */
    public function setHandleParagraphs(bool $bool);

    /**
     * Задаёт режим выравнивания текста.
     *
     * По умолчанию используется настройка файла шрифта.
     *
     * @param  int $mode Выравнивание.
     *                   Допустимые значения: {@see JUSTIFICATION_LEFT}, {@see JUSTIFICATION_CENTER} и {@see JUSTIFICATION_RIGHT}.
     * @return self
     */
    public function setJustification(int $mode);

    /**
     * Задаёт режим направления текста.
     *
     * По умолчанию используется настройка файла шрифта.
     * Если выравнивание не определено, текст, написанный справа налево, автоматически выравнивается по правому краю.
     *
     * @param  bool $bool Логическое значение, указывающее, в каком направлении пишется текст.
     * @return self
     */
    public function setBackward(bool $bool);

    /**
     * Задаёт расстояние между символами.
     *
     * @param  int $stretching Расстояние.
     * @return self
     */
    public function setStretching(int $stretching);

    #endregion

    /**
     * Возвращает стилизованный текст.
     *
     * @param  string $text Текст.
     * @param  string $encoding Кодировка.
     * @return string
     */
    public function render(string $text, string $encoding = 'utf-8');

    /**
     * Выводит на экран стилизованный текст.
     *
     * @param  string $text Текст.
     * @param  string $encoding Кодировка.
     * @return void
     */
    public function write(string $text, string $encoding = 'utf-8');
}
