# Figlet

Представление текста в виде рисунка **ASCII**.  

## Установка

```bash
conpposer require the-alex-mark/figlet
```

## Использование

```php
$figlet = new Figlet();
$figlet

    // Указание иной директории хранения файлов для использования пользовательского набора шрифтов
    ->setStorage(__DIR__ . '/../assets/fonts')

    // Параметры отображения текста
    ->setFont('slant')
    ->setWidth(200)
    ->setSmushing(FigletContract::SM_BIGX)
    ->setHandleParagraphs(false)
    ->setJustification(FigletContract::JUSTIFICATION_LEFT)
    ->setBackward(false)
    ->setStretching(0)
    ->write('Hello World!');

// Вспомогательный метод
echo figlet('Hello World!', [ 'smushing' => FigletContract::SM_BIGX ]);
```
```
    __  __       __ __          _       __              __     __ __
   / / / /___   / // /____     | |     / /____   _____ / /____/ // /
  / /_/ // _ \ / // // __ \    | | /| / // __ \ / ___// // __  // /
 / __  //  __// // // /_/ /    | |/ |/ // /_/ // /   / // /_/ //_/
/_/ /_/ \___//_//_/ \____/     |__/|__/ \____//_/   /_/ \__,_/(_)
```
