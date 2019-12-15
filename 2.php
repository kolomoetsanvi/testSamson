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
    if(!isset($products->Товар)) exit("В файле нет товаров");
    foreach ($products->Товар as $goods) {
        // создание строки запроса
        $code = htmlentities(mysqli_real_escape_string($link, $goods['Код']));
        $title = htmlentities(mysqli_real_escape_string($link, $goods['Название']));
        if (!isset($code) || !is_int((int)$code) || !isset($title)) continue;
        $query ="INSERT INTO a_product VALUES(NULL, ".$code.", \"".$title."\")";
        // выполняем запрос
        $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
        $idProduct = mysqli_insert_id($link);
        if(!$result) throw new Exception('Товар не добавлен в БД');


            //Проходим по ценам товара
            if (isset($goods->Цена)) {
                foreach ($goods->Цена as $price) {
                    // создание строки запроса
                    $type = htmlentities(mysqli_real_escape_string($link, $price['Тип']));
                    $price = htmlentities(mysqli_real_escape_string($link, $price));
                    if (!isset($type) || !isset($price)  || !is_float((float)$price)) continue;
                    $query = "INSERT INTO a_price VALUES(NULL, $idProduct, \"" . $type . "\", \"" . $price . "\")";
                    // выполняем запрос
                    $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
                    if (!$result) throw new Exception('Цены не добавлены в БД');
                }//foreach ($goods->Цена as $price)
            }//if

            //Проходим по свойствам товара
            if(isset($goods->Свойства) && $goods->Свойства->children()->count() > 0) {
                foreach ($goods->Свойства->children() as $property) {

                    ($property['ЕдИзм'] == NULL) ? $unit = "NULL" : $unit = (string)$property['ЕдИзм'];
                    // создание строки запроса
                    $name = $property->getName();
                    $name = htmlentities(mysqli_real_escape_string($link, $name));
                    $unit = htmlentities(mysqli_real_escape_string($link, $unit));
                    $property = htmlentities(mysqli_real_escape_string($link, $property));
                    if (!isset($name) || !isset($unit) || !isset($property)) continue;
                    $query = "INSERT INTO a_property VALUES(NULL, $idProduct, \"" . $name . "\", \"" . $unit . "\", \"" . $property . "\")";
                    // выполняем запрос
                    $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
                    if (!$result) throw new Exception('Свойства не добавлены в БД');
                }//foreach ($goods->Свойства->children() as $property)
            }//if

             //Проходим по разделам товара
            if (isset($goods->Разделы) && $goods->Разделы->Раздел->count() > 0) {
                foreach ($goods->Разделы->Раздел as $section) {
                    categoryTree($section, 0, $link, $idProduct); // рекурсивная функция
                }//foreach ($goods->Разделы->Раздел as $section)
            }//if
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
        $code = htmlentities(mysqli_real_escape_string($link, $code));
        $sect = htmlentities(mysqli_real_escape_string($link, $section));
        $parent_id = htmlentities(mysqli_real_escape_string($link, $parent_id));
        if (isset($code) && is_int((int)$code) && isset($section) && isset($parent_id)) {
            $query = "INSERT INTO a_category VALUES(NULL, " . $code . ", \"" . $sect . "\", $parent_id)";
            // выполняем запрос
            $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
            if (!$result) throw new Exception('Категория не добавлены в БД');
            $idCategory = mysqli_insert_id($link);
        }//if
    }


    //Проверяем есть ли вложенные категории
    if($section->children()->count() > 0){
         //Проходим по дочерним разделам
        // рекрсивный вызов функции
        foreach ($section->children() as $item) {
            categoryTree($item  , $idCategory, $link, $idProduct);
        }//foreach ($goods->Разделы->Раздел as $section)
    }
    else{
        //добавим товар в категорию
        $idProduct = htmlentities(mysqli_real_escape_string($link, $idProduct));
        $idCategory = htmlentities(mysqli_real_escape_string($link, $idCategory));
        if (isset($idProduct) && isset($idCategory)) {
            $query = "INSERT INTO a_product_category VALUES(NULL, $idProduct, $idCategory)";
            // выполняем запрос
            $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
            if (!$result) throw new Exception('Товар не добавлен в категорию');
        }// if
    }// if

}//categoryTree($parent_id)
//========================================================================
//========================================================================




//Реализовать функцию exportXml($a, $b).
// $a – путь к xml файлу вида (структура файла приведена ниже),
// $b – код рубрики. Результат ее выполнения: выбрать из БД товары
// (и их характеристики, необходимые для формирования файла) выходящие в рубрику $b
// или в любую из всех вложенных в нее рубрик, сохранить результат в файл $a
function  exportXml($a, $b)
{
    //Данные для подключение к базе данных
    $host     = 'localhost';     // адрес сервера
    $database = 'test_samson';  // имя базы данных
    $user     = 'root';          // имя пользователя
    $password = '';   // пароль

    // подключаемся к серверу
    $link = mysqli_connect($host, $user, $password, $database)
    or die("Ошибка " . mysqli_error($link));

    $query ="SELECT * FROM a_category";
    $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
    if(!$result) throw new Exception('При проверке категории возникла ошибка');

    //получаем с БД массив всех категорий
    $arr_category = array();
    while($row = mysqli_fetch_assoc($result)){
        $arr_category[] = $row;
    }

    $b = htmlentities(mysqli_real_escape_string($link, $b));
    //Получаем id заданной категории
    $query ="SELECT id FROM a_category
             WHERE code like \"".$b."\" LIMIT 1";
    $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
    if(!$result) throw new Exception('При проверке категории возникла ошибка');
    $idParentCategory = mysqli_fetch_row($result)[0];



    //Получаем массив с id всех категорий, вложенных в заданную
    $idCategories = getCategoryId($arr_category, $idParentCategory);
    $idCategories = rtrim($idCategories, ',');

    // Получаем список продуктов, которые входят в подкатегории
    // указанной категории.
    $products = getProducts($idCategories, $link);

    //!!!!!!!!!!
    //формируем XML контент
    $file = "exportResult.xml";
    $doc = new DOMDocument('1.0', 'utf-8');
    $content = $doc->createElement('Товары');
    $doc->appendChild($content);
    foreach ($products as $product){
       $element = $doc->createElement('Товар');
            $atr1 = $doc->createAttribute('Код');
            $atr1->value = $product['code'];
        $element->appendChild($atr1);
            $atr2 = $doc->createAttribute('Название');
            $atr2->value = $product['title'];
       $element->appendChild($atr2);
       $Prod = $content->appendChild($element);




        // получаем цены для данного товара
        $query ="SELECT * FROM a_price WHERE product_id like ".$product['id'];
        $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
        if(!$result) throw new Exception('При проверке категории возникла ошибка');
        $prices = array();
        while($row = mysqli_fetch_assoc($result)){
            $prices[] = $row;
        }//while
        foreach ($prices as $price){
            $element = $doc->createElement('Цена', $price['price']);
                $atr = $doc->createAttribute('Тип');
                $atr->value = $price['type'];
            $element->appendChild($atr);
            $pr = $Prod->appendChild($element);
        }//foreach ($prices as $price)
        //---------------------------------------------------------

        // получаем свойства для данного товара
        $query ="SELECT * FROM a_property WHERE product_id like ".$product['id'];
        $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
        if(!$result) throw new Exception('При проверке категории возникла ошибка');
        $properties = array();
        while($row = mysqli_fetch_assoc($result)){
            $properties[] = $row;
        }//while

        $element = $doc->createElement('Свойства');
        $prop = $Prod->appendChild($element);
        foreach ($properties as $property){
            $element = $doc->createElement($property['property'], $property['value']);
            if ($property['unit'] != "NULL"){
                $atr = $doc->createAttribute('ЕдИзм');
                $atr->value = $property['unit'];
                $element->appendChild($atr);
            }
            $item = $prop->appendChild($element);
        }//foreach ($prices as $price)
        //---------------------------------------------------------


        //Формируем разделы к которым относится продукт
        $element = $doc->createElement('Разделы');
        $sections = $Prod->appendChild($element);

        // формируем массив с категориями, в котором ключ - id категории
        $query ="SELECT * FROM a_category";
        $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
        if(!$result) throw new Exception('При проверке категории возникла ошибка');

        //получаем с БД массив всех категорий
        $all_category = array();
        while($row = mysqli_fetch_assoc($result)){
            $all_category[$row['id']] = $row;
        }

        //Получаем список категорий в которых непосредственно хранится продукт
        $productCategoty = getProductCategoty($product['id'], $link);

        //Для каждой категории продукта готовим список родительских категорий в которые она входит
        // Потом раскручиваем список с самой верхней категории и формируем xml документ
        foreach ($productCategoty as $category){

          $tempArr = getTempArr($all_category, $category);
          $tempArr = array_reverse($tempArr);

         editCategory($doc, $sections, $tempArr, 0);
        }//foreach ($productCategoty as $category){

    }//foreach ($products as $product)



    //записываем данные  в xml файл
    $doc->save($file);
   // закрываем подключение
    mysqli_close($link);

}//exportXml($a, $b)

//===================================================================================
//вспомогательная функция. Получаем id всех подкатегорий входящи в заданную категорию
function  getCategoryId($array, $id){
    $data = $id.',';
    foreach ($array as $item){
        if($item['parent_id'] == $id){
            $data .= $item['id'].',';
            $data .= getCategoryId($array, $item['id']);
        }
    }
    return $data;
} //function  getCategoryId($array, $kodCat

//=======================================================================
//вспомогательная функция. Получаем  товары входящие в подкатегории
function getProducts($idCategories, $link){
        // Получаем список id товаров
        $query ="SELECT product_id FROM a_product_category
                 WHERE category_id IN($idCategories)";
        $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
        if(!$result) throw new Exception('При проверке категории возникла ошибка');
        $idProducts = '';
        while($row = mysqli_fetch_row($result)){
            $idProducts .= $row[0].',';
        }
    $idProducts = rtrim($idProducts, ',');

    // получаем масси впродуктов в заданной категории
    if($idProducts){
        //Получаем товары из базы данных
        $query ="SELECT * FROM a_product WHERE id IN($idProducts) ORDER BY title";
    }
    else{
        $query ="SELECT * FROM a_product ORDER BY title";
    }
    $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
    if(!$result) throw new Exception('При проверке категории возникла ошибка');

    $products = array();
    while($row = mysqli_fetch_assoc($result)){
        $products[] = $row;
    }

   return $products;
 }//function getProducts


//======================================================================
//вспомогательная функция. Получаем  список категорий в которых непосредственно хранится продукт
function getProductCategoty($idProduct, $link){
        // Получаем список id категорий
        $query ="SELECT category_id FROM a_product_category WHERE product_id like ".$idProduct;
        $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
        if(!$result) throw new Exception('При проверке категории возникла ошибка');
        $idCategory = array();
        while($row = mysqli_fetch_row($result)){
            $idCategory[] = $row[0];
        }

   return $idCategory;
 }//getProductCategoty

//======================================================================
// Вспомогательная функция - получаем массив категория в которые входит корневая категория по нарастанию
function getTempArr($arr, $id){

    foreach ($arr as $item){
        if($item['parent_id'] == $id){
            $data[] = array ('title' => $item['title'],'code'=>$item['code']);
            $data = array_merge($data, getCategoryId($arr, $item['id']));
        }
        else {
            $data[] = array ('title' => $arr[$id]['title'],'code'=>$arr[$id]['code']);
            return $data;
        }
    }
    return $data;
} //function  getTempArr($arr, $id)


//======================================================================
//Вспомогательная функция. Строит категории товара в XML документе
function editCategory($doc, $sections, $tempArr, $index){
    if (isset($tempArr[$index])){
        $element = $doc->createElement('Раздел', $tempArr[$index]['title']);
        $section = $sections->appendChild($element);
        if ($tempArr[$index]['code'] != NULL)
            $atr = $doc->createAttribute('Код');
            $atr->value = $tempArr[$index]['code'];
            $section->appendChild($atr);
        $index ++;
        editCategory($doc, $section, $tempArr, $index);
    }
}//function editCategory($arr, $id)


//=======================================================================
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
//
//
//    echo "</br></br>========================================================================</br></br>";


//    $xmlFile = 'Products.xml';
//    importXml($xmlFile);

//   $resultFile = 'ResultFile.xml';
//   exportXml($resultFile, 101);


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


