
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width" />

      <title>Задание 1</title>

</head>

<body>

<!-- ############################################################################### -->
<!-- ############################################################################### -->
 <?php


	//Реализовать функцию findSimple ($a, $b). $a и $b – целые положительные числа.
    //Результат ее выполнение: массив простых чисел от $a до $b.
	function findSimple($a, $b)
	{
		$myArray = array();

		if($b > $a)
			for ($i = $a; $i < $b + 1; $i++) $myArray[] = $i;
		elseif($a == $b)
			$myArray[] = $a;
		else
			for ($i = $a; $i > $b - 1; $i--) $myArray[] = $i;

		return $myArray;
	}//function findSimple($a, $b)
	//========================================================================



    //Реализовать функцию createTrapeze($a). $a – массив положительных чисел, количество элементов кратно 3.
    // Результат ее выполнение: двумерный массив (массив состоящий из ассоциативных массива с ключами a, b, c).
    // Пример для входных массива [1, 2, 3, 4, 5, 6] результат [[‘a’=>1,’b’=>2,’с’=>3],[‘a’=>4,’b’=>5 ,’c’=>6]].
	function createTrapeze($a)
	{
		$myArray = array();
		
		for ($i = 0; $i < count($a); $i+=3) {
			$tempArr =["a" => $a[$i], "b" => $a[$i+1], "c" => $a[$i+2]];
			$myArray[] = $tempArr;
		}		
		
		return $myArray;
	}//function createTrapeze($a)
    //========================================================================


    // Реализовать функцию squareTrapeze($a). $a – массив результата выполнения функции createTrapeze().
    // Результат ее выполнение: в исходный массив для каждой тройки чисел добавляется дополнительный ключ s,
    // содержащий результат расчета площади трапеции со сторонами a и b, и высотой c.
	function squareTrapeze($a)
	{
		$myArray = $a;
		foreach ($myArray as $key => $val)
		{
		    $s = 0.5*(int)$val["c"] * ((int)$val["a"] + (int)$val["b"]);
            $myArray[$key] += ['s' => $s];
		}
		return $myArray;
	}//function squareTrapeze($a)
 //========================================================================


	//Реализовать функцию getSizeForLimit($a, $b). $a – массив результата выполнения функции squareTrapeze(), $b – максимальная площадь.
    // Результат ее выполнение: массив размеров трапеции с максимальной площадью, но меньше или равной $b.
	function getSizeForLimit($a, $b)
	{
		$tempArray = $a[0];
		$maxS = $a[0]['s'];
		foreach ($a as $key => $val)
		{
			if ($val["s"] > $maxS && $val["s"] <= $b) $tempArray = $val;
		}
		
		return $tempArray;
	}//function getSizeForLimit($a, $b)
 //========================================================================


	
	//Реализовать функцию getMin($a). $a – массив чисел.
    // Результат ее выполнения: минимальное числа в массиве (не используя функцию min, ключи массив может быть ассоциативный).
	function getMin($a)
	{
		$min = $a[0];
        foreach ($a as $val)
        {
			if($val < $min) $min = $val;
		}
		return $min;
	}//function getMin($a)
 //========================================================================


	 //Реализовать функцию printTrapeze($a). $a – массив результата выполнения функции squareTrapeze().
     // Результат ее выполнение: вывод таблицы с размерами трапеций, строки с нечетной площадью трапеции
    // отметить любым способом.
	function printTrapeze($a)
	{
		echo "<table cellspacing=\"2\" border=\"1\" cellpadding=\"5\" width=\"600\"><thead><tr><td>a</td><td>b</td><td>c</td><td>s</td></thead></tr><tbody>";

		foreach ($a as $tempArr => $item){
			if ($item["s"]%2 != 0 || $item["s"] - (int)$item["s"] != 0 ) echo "<tr style='background: grey'>";
			else echo "<tr>";
		
			echo "<td>".$item["a"]."</td>";
			echo "<td>".$item["b"]."</td>";
			echo "<td>".$item["c"]."</td>";
			echo "<td>".$item["s"]."</td>";
			echo "</tr>";
		}
		echo "</tbody></table>";
	}//function printTrapeze($a)
 //========================================================================
	

    //Реализовать абстрактный класс BaseMath содержащий 3 метода: exp1($a, $b, $c) и exp2($a, $b, $c),getValue().
    // Метода exp1 реализует расчет по формуле a*(b^c). Метода exp2 реализует расчет по формуле (a/b)^c.
    // Метод getValue() возвращает результат расчета класса наследника.
	abstract class BaseMath{
		
		protected function exp1($a, $b, $c)
		{
			return $a * ($b ^ $c);
		}
 
		protected function exp2($a, $b, $c)
		{
			return ($a / $b) ^ $c;
		}
 
		abstract public function getValue();
 
	}//abstract class BaseMath
    //========================================================================


    //Реализовать класс F1 наследующий методы BaseMath, содержащий конструктор с параметрами ($a, $b, $c)
    // и метод getValue(). Класс реализует расчет по формуле f=(a*(b^c)+(((a/c)^b)%3)^min(a,b,c)).
     //!!!!!!!!!!!!!!!
    // возможно в задании опечатка в формуле: f=(a*(b^c)+(((a/c)^b)%3)^min(a,b,c))
    // а именно (a/c)^b), предполагаю что верно (a/b)^c для того чтобы в методе getValue() вызывать exp1 и exp2
    // или это тест на внимательность :)
   //!!!!!!!!!!!!!!!!
   // делаю как указано в задании.
	class F1 extends BaseMath
	{
		protected $a;
		protected $b;
		protected $c;
 
		public function __construct($a, $b, $c)
		{
			$this->a = $a;
			$this->b = $b;
			$this->c = $c;
		}
	
		public function getValue()
		{
			return ($this->exp1($this->a, $this->b, $this->c) + ((($this->a / $this->c)^$this->b) % 3) ^ min($this->a, $this->b, $this->c));
 		}
	}//class F1 extends BaseMath
      //========================================================================
    //========================================================================

    // Демонстрация работы
	try
    {
        echo '<p>a = 2; b = 2.</p>';
        print_r(findSimple(2, 2));
        echo "</br>";
        echo '<p>a = 2; b = 10.</p>';
        print_r(findSimple(2, 10));
        echo "</br>";
        echo '<p>a = 10; b = 2.</p>';
        print_r(findSimple(10, 2));

        echo "</br></br>========================================================================</br></br>";

        $a = array(1, 2, 3);
        print_r($a);
        echo "</br>";
        print_r(createTrapeze($a));
        echo "</br>";
        echo "</br>";
        $a = array(1, 2, 3, 4, 5, 6, 7, 8, 9);
        print_r($a);
        echo "</br>";
        $createTrapeze = createTrapeze($a);
        print_r($createTrapeze);

        echo "</br></br>========================================================================</br></br>";

        print_r($a);
        echo "</br>";
        print_r($createTrapeze);
        echo "</br>";
        $squareTrapeze = squareTrapeze($createTrapeze);
        print_r($squareTrapeze);

        echo "</br></br>========================================================================</br></br>";

        $b = 30;
        print_r($squareTrapeze);
        echo "</br>";
        echo 'b = '; print_r($b);
        echo "</br>";
        print_r(getSizeForLimit($squareTrapeze, $b));
        echo "</br>";

        $b = 20;
        echo 'b = '; print_r($b);
        echo "</br>";
        print_r(getSizeForLimit($squareTrapeze, $b));


        echo "</br></br>========================================================================</br></br>";

        $a = array();
        for($i = 0; $i < 10; $i++) $a[] = rand(0, 20);
        print_r($a);
        echo "</br>";
        echo 'min = ';print_r(getMin($a));


        echo "</br></br>========================================================================</br></br>";

        printTrapeze($squareTrapeze);


        echo "</br></br>========================================================================</br></br>";


        $f1 = new F1(1, 2, 3);
        print_r($f1->getValue());


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