<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width" />

    <title>Задание 2</title>

</head>

<body>

<!-- ############################################################################### -->
<!-- ############################################################################### -->
<?php

//Реализовать функцию convertString($a, $b).
// Результат ее выполнение: если в строке $a содержится 2 и более подстроки $b,
// то во втором месте заменить подстроку $b на инвертированную подстроку
function convertString($a, $b)
{

    $myStr =  mb_strtolower($a);
    $mySubStr = mb_strtolower($b);

    if(substr_count($myStr, $mySubStr) < 2) return $a;

    //ищем позицию первого вхождени яподстроки
    $subStrFirstPosition = mb_stripos($a, $b);
    //смещаемся относительно начала основной строки на позицию первого вхождения + длинна подстроки
    // теперь 1-е вхождени еи будет 2-м (искомым)
    $subStrSectPosition = mb_stripos($a, $b, $subStrFirstPosition + strlen($b));

    //инвертируем подстроку
    $mySubStr = '';
    for ($i = mb_strlen($b); $i>=0; $i--) {
        $mySubStr .= mb_substr($b, $i, 1);
    }

    $myStr = mb_substr($a, 0, $subStrSectPosition);
    $myStr .= $mySubStr;
    $myStr .= mb_substr($a, $subStrSectPosition + mb_strlen($b));


    return $myStr;
}//function convertString($a, $b)
//========================================================================




// Демонстрация работы
try
{
    $a = "Мама мыла раму";
    $b = "мыла";
    echo "<p>Строка: $a;</p>";
    echo "<p>Подстрока: $b;</p>";
    print_r(convertString($a, $b));
    echo "</br>";

    $a = "Если бы да кабы во рту выросли грибы";
    $b = "бы";
    echo "<p>Строка: $a;</p>";
    echo "<p>Подстрока: $b;</p>";
    print_r(convertString($a, $b));

    echo "</br></br>========================================================================</br></br>";



} catch (Exception $ex)
{
    $msg = $ex->getMessage();
    $line = $ex->getLine();
    echo "<span class='red-text'>$msg</span> в строке $line";
} // try-catch


?>
<!-- ############################################################################### -->
<!-- ############################################################################### -->




</body>
</html>
