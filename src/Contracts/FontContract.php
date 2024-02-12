<?php

namespace ProgLib\Figlet\Contracts;

interface FontContract {

    #region Constants

    /**
     * Поддерживаемая сигнатура файла
     */
    const FIGLET_SIGNATURE = 'flf2a';

    #endregion

    #region Parameters

    /**
     * Возвращает сигнатуру.
     *
     * Всегда соответствует значению {@see FIGLET_SIGNATURE}.
     *
     * @return string
     */
    public function getSignature();

    /**
     * ...
     *
     * @return string
     */
    public function getHardblank();

    /**
     * Возвращает высоту символа.
     *
     * @return int
     */
    public function getHeight();

    /**
     * ...
     *
     * @return int
     */
    public function getBaseline();

    /**
     * ...
     *
     * @return int
     */
    public function getMaxLength();

    /**
     * ...
     *
     * @return int
     */
    public function getOldLayout();

    /**
     * ...
     *
     * @return int
     */
    public function getCommentLines();

    /**
     * ...
     *
     * @return int
     */
    public function getPrintDirection();

    /**
     * ...
     *
     * @return int
     */
    public function getFullLayout();

    /**
     * ...
     *
     * @return int
     */
    public function getCodetagCount();

    #endregion

    /**
     * Возвращает список символов.
     *
     * @return array
     */
    public function getCharacters();
}
