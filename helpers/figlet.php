<?php

use ProgLib\Figlet\Figlet;

if (!function_exists('figlet')) {

    /**
     * Возвращает текст в виде рисунка **ASCII**.
     *
     * @param  string $text Текст.
     * @param  array $options Параметры отображения текста.
     * @return string
     */
    function figlet($text, $options = []) {
        $figlet = new Figlet();

        if (isset($options['font']))
            $figlet->setFont($options['font']);

        if (isset($options['width']))
            $figlet->setWidth($options['width']);

        if (isset($options['smushing']))
            $figlet->setSmushing($options['smushing']);

        if (isset($options['handle_paragraphs']))
            $figlet->setHandleParagraphs($options['handle_paragraphs']);

        if (isset($options['justification']))
            $figlet->setJustification($options['justification']);

        if (isset($options['backward']))
            $figlet->setBackward($options['backward']);

        if (isset($options['stretching']))
            $figlet->setStretching($options['stretching']);

        return $figlet->render($text);
    }
}
