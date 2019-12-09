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
    $content = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Товары/>');
    foreach ($products as $product){
       $Prod = $content->addChild('Товар');
       $Prod->addAttribute('Код', $product['code'] );
       $Prod->addAttribute('Название', $product['title'] );


        // получаем цены для данного товара
        $query ="SELECT * FROM a_price WHERE product_id like ".$product['id'];
        $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
        if(!$result) throw new Exception('При проверке категории возникла ошибка');
        $prices = array();
        while($row = mysqli_fetch_assoc($result)){
            $prices[] = $row;
        }//while
        foreach ($prices as $price){
            $pr = $Prod->addChild('Цена', $price['price']);
            $pr->addAttribute('Тип', $price['type']);
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

        $prop = $Prod->addChild('Свойства');
        foreach ($properties as $property){
            $item = $prop->addChild($property['property'], $property['value']);
           // if ($property['unit'] != NULL) $item->addAttribute('ЕдИзм', $property['unit']);
        }//foreach ($prices as $price)
        //---------------------------------------------------------


        //Формируем разделы к которым относится продукт
        $sections = $Prod->addChild('Разделы');

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

          editCategory($sections, $tempArr, 0);
        }//foreach ($productCategoty as $category){

    }//foreach ($products as $product)



    //записываем данные  в xml файл
     $content->asXML($file);
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
function editCategory($sections, $tempArr, $index){
    if (isset($tempArr[$index])){
        $section = $sections->addChild('Раздел', $tempArr[$index]['title']);
        if ($tempArr[$index]['code'] != NULL)
            $section->addAttribute('Код', $tempArr[$index]['code'] );
        $index ++;
        editCategory($section, $tempArr, $index);
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


    $xmlFile = 'Products.xml';
    importXml($xmlFile);

   $resultFile = 'ResultFile.xml';
   exportXml($resultFile, 103);


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


