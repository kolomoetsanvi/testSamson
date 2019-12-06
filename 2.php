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




//Реализовать функцию importXml($a). $a – путь к xml файлу .
// Результат ее выполнения: прочитать файл $a и импортировать его в созданную БД.
function  importXml($a)
{
    //Данные для подключение к базе данных
    $host     = 'localhost';     // адрес сервера
    $database = 'test_samson';  // имя базы данных
    $user     = 'root';          // имя пользователя
    $password = '';   // пароль

    // подключаемся к серверу
    $link = mysqli_connect($host, $user, $password, $database)
    or die("Ошибка " . mysqli_error($link));


    $products = simplexml_load_file($a);

    //Проходим по товарам
    foreach ($products->Товар as $goods) {
        // создание строки запроса
        $query ="INSERT INTO a_product VALUES(NULL, ".$goods['Код'].", \"".$goods['Название']."\")";
        // выполняем запрос
        $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
        $idProduct = mysqli_insert_id($link);
        if(!$result) throw new Exception('Товар не добавлен в БД');


            //Проходим по ценам товара
            foreach ($goods->Цена as $price) {
                // создание строки запроса
                $query ="INSERT INTO a_price VALUES(NULL, $idProduct, \"".$price['Тип']."\", \"".$price."\")";
                // выполняем запрос
                $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
                if(!$result) throw new Exception('Цены не добавлены в БД');
            }//foreach ($goods->Цена as $price)


            //Проходим по свойствам товара
            foreach ($goods->Свойства->children() as $property) {

                ($property['ЕдИзм'] == NULL)? $unit = "NULL": $unit = (string)$property['ЕдИзм'];
                // создание строки запроса
                $query ="INSERT INTO a_property VALUES(NULL, $idProduct, \"".$property->getName()."\", \"".$unit."\", \"".$property."\")";
                // выполняем запрос
                $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
                if(!$result) throw new Exception('Свойства не добавлены в БД');
            }//foreach ($goods->Свойства->children() as $property)

             //Проходим по разделам товара
             foreach ($goods->Разделы->Раздел as $section) {
                 categoryTree($section, 0, $link, $idProduct); // рекурсивная функция
            }//foreach ($goods->Разделы->Раздел as $section)

    }//foreach ($products->Товар as $goods)


    // закрываем подключение
    mysqli_close($link);

}//importXml($a)

// Вспомогательная функция - формирует дерево категорий
function categoryTree($section, $parent_id, $link, $idProduct)
{
    // Если категории не присвоен код сохраняем в таблице NULL
    ($section['Код'] == NULL)? $code = "NULL": $code = (string)$section['Код'];

    //Проверяем есть ли указанныая категория в базе данных
    // если нет добавляем ее в таблицу с категориями
    $query ="SELECT id FROM a_category
             WHERE title like \"".$section."\"
             AND code ".(($code == "NULL")? "IS NULL": ("like".$code))." LIMIT 1";
    $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
    if(!$result) throw new Exception('При проверке категории возникла ошибка');
    $idCategory = mysqli_fetch_row($result)[0];

    if($idCategory == NULL){ //если нет категории с таким названием
        // создание строки запроса
        $query ="INSERT INTO a_category VALUES(NULL, ".$code.", \"".$section."\", $parent_id)";
        // выполняем запрос
        $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
        if(!$result) throw new Exception('Категория не добавлены в БД');
        $idCategory = mysqli_insert_id($link);
    }


    //Проверяем есть ли вложенные категории
    if($section->children()->count() > 0){
         //Проходим по дочерним разделам
        // рекрсивный вызов функции
        foreach ($section->children() as $section) {
            categoryTree($section, $idCategory, $link, $idProduct);
        }//foreach ($goods->Разделы->Раздел as $section)
    }
    else{
        //добавим товар в категорию
        $query ="INSERT INTO a_product_category VALUES(NULL, $idProduct, $idCategory)";
        // выполняем запрос
        $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
        if(!$result) throw new Exception('Товар не добавлен в категорию');
    }// if

}//categoryTree($parent_id)

//========================================================================
//========================================================================



// ###############################################################################
// ###############################################################################

// Демонстрация работы
try
{
//    $a = "Мама мыла раму";
//    $b = "мыла";
//    echo "<p>Строка: $a;</p>";
//    echo "<p>Подстрока: $b;</p>";
//    print_r(convertString($a, $b));
//    echo "</br>";
//
//    $a = "Если бы да кабы во рту выросли грибы";
//    $b = "бы";
//    echo "<p>Строка: $a;</p>";
//    echo "<p>Подстрока: $b;</p>";
//    print_r(convertString($a, $b));
//
//
//    echo "</br></br>========================================================================</br></br>";
//
//    $arr = array(
//        array('a'=>2,'b'=>1),
//        array('a'=>5, 'b'=>8),
//        array('a'=>3,'b'=>7),
//        array('a'=>2,'b'=>9)
//    );
//
//
//    var_dump($arr);
//    echo "</br>";
//    echo "</br>";
//    echo "<p>Массив отсортирован по столбцу b</p>";
//    var_dump(mySortForKey($arr, 'b'));
//    echo "</br>";
//    echo "<p>Массив отсортирован по столбцу a</p>";
//    var_dump(mySortForKey($arr, 'a'));
//
//    echo "</br></br>";
//    echo "<p>Массив без индекса b в одном из вложенных массивово</p>";
//    $arr = array(
//        array('a'=>2,'b'=>1),
//        array('a'=>5, 8),
//        array('a'=>3,'b'=>7),
//        array('a'=>2,'b'=>9)
//    );
//
//
//    var_dump($arr);
//    echo "</br>";
//    echo "</br>";
//    echo 'В массиве с указанным индексом нет ключа b: ';
//    var_dump(mySortForKey($arr, 'b'));


    echo "</br></br>========================================================================</br></br>";


    $xmlFile = 'Products.xml';
    importXml($xmlFile);



} catch (Exception $ex)
{
    $msg = $ex->getMessage();
    $line = $ex->getLine();
    echo "<span class='red-text'>$msg</span> в строке $line";
} // try-catch


?>
<!-- ############################################################################### -->
<!-- ############################################################################### -->



<!--В Xml файле в свойствах товара есть единицы измерения. Про них в задании не сказано-->
<!--Разделы Код -?-->
</body>
</html>


