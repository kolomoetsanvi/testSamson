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
//========================================================================


//Реализовать функцию mySortForKey($a, $b). $a – двумерный массив вида [['a'=>2,'b'=>1],['a'=>1,'b'=>3]], $b – ключ вложенного массива.
// Результат ее выполнения: двумерном массива $a отсортированный по возрастанию значений для ключа $b.
// В случае отсутствия ключа $b в одном из вложенных массивов, выбросить ошибку класса Exception с индексом неправильного массива.
function mySortForKey($a, $b)
{
    $myArr = $a;

    // Обходим все элементы массива в цикле
    foreach ($myArr as $key => $val) {
        // Проверяем чтобы во всех массивах был ключ $b
        if (!array_key_exists($b, $val)) {
            throw new Exception($key);
        }
    }//foreach

    usort($myArr, build_sorter($b));

    return $myArr;
}//function mySortForKey($a, $b)



//создаем функцию сортировки для usort
function build_sorter($key) {
    return function ($x, $y) use ($key){
        if ($x[$key] == $y[$key])return 0;
        return ($x[$key] > $y[$key]) ? 1 : -1;
    };//function ($x, $y)
};//function build_sorter($key)
//========================================================================
//========================================================================







// ###############################################################################
// ###############################################################################

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

    $arr = array(
        array('a'=>2,'b'=>1),
        array('a'=>5, 'b'=>8),
        array('a'=>3,'b'=>7),
        array('a'=>2,'b'=>9)
    );


    var_dump($arr);
    echo "</br>";
    echo "</br>";
    echo "<p>Массив отсортирован по столбцу b</p>";
    var_dump(mySortForKey($arr, 'b'));
    echo "</br>";
    echo "<p>Массив отсортирован по столбцу a</p>";
    var_dump(mySortForKey($arr, 'a'));

    echo "</br></br>";
    echo "<p>Массив без индекса b в одном из вложенных массивово</p>";
    $arr = array(
        array('a'=>2,'b'=>1),
        array('a'=>5, 8),
        array('a'=>3,'b'=>7),
        array('a'=>2,'b'=>9)
    );


    var_dump($arr);
    echo "</br>";
    echo "</br>";
    echo 'В массиве с указанным индексом нет ключа b: ';
    var_dump(mySortForKey($arr, 'b'));


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
