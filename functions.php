<?php
function convertToMatrixCoordinates($index){
	$index = $index - floor($index / 11) - 11;
	$array = array($index % 10, floor($index / 10));
	return $array;
}
function sign( $number ) { 
    return ( $number > 0 ) ? 1 : ( ( $number < 0 ) ? -1 : 0 ); 
} 
function convertToIndex($y, $x){
	$index = $y * 10 + $x;
	$index = $index + 11 + floor(($index + 10)/ 10);
	return $index;
}
function getMissArray($indexDeadShip, $shipMatrix){
	$current = convertToMatrixCoordinates($indexDeadShip);
	$y = $current[1];
	$x = $current[0];
	$_SESSION[$shipMatrix][$y][$x] = "died";
	$array = array();
	for ($i = 0; $i < 3; $i++)
		for ($j = 0; $j < 3; $j++){
			if (checkOutOfRange($y - 1 + $i, $x - 1 + $j)){
				if (($_SESSION[$shipMatrix][$y - 1 + $i][$x - 1 + $j]) === 0){
					$_SESSION[$shipMatrix][$y - 1 + $i][$x - 1 + $j] = "missed";
					array_push($array, convertToIndex($y - 1 + $i, $x - 1 + $j));
					//echo json_encode($variable);
				}
			}
		}
	return $array;
		
}
function checkOutOfRange($y, $x){ #проверяем координату на выход за пределы массива, если вышел false иначе true
	if (($y > 9) || ($y < 0) || ($x > 9) || ($x < 0)){
		return false;
	}
	else return true;
}
function checkShipInRadius($y, $x, $array){ #проверяем на наличие кораблей в радиусе 1, если кораблей нет false иначе true
	if (!(checkOutOfRange($y, $x))){
		return true;
	}
	for ($i = 0; $i < 3; $i++)
		for ($j = 0; $j < 3; $j++){
			if (checkOutOfRange($y - 1 + $i, $x - 1 + $j)){
				if (($array[$y - 1 + $i][$x - 1 + $j]) != 0)
					return true;
			}
		}
	return false;
}
function printArray($array){ #вывод массива на экран
	for ($i = 0; $i < 10; $i++){
		for ($j = 0; $j < 10; $j ++)
			echo $array[$i][$j]." ";
		echo "<br />";
	}
}
function randomRoute(){ #задаем случайное направление кораблю, либо вертикально, либо горизонтально
	$value = rand(0, 1);
	switch($value){
		case 0:
			return "vertical";
			break;
		case 1:
			return "horizontal";
			break;
	}
}
function fillZerosMatrix(){
	for ($i = 0; $i < 10; $i++)
		for ($j = 0; $j < 10; $j ++)
			$array[$i][$j] = 0;
	return $array;
}
function generateEnemyShipMatrix(){ # создание матрицы кораблей бота
#Заполнили нулями
	$array = fillZerosMatrix();
#создаем ассоциативный массив, где key = n-палубник; value - количество кораблей на поле		
	$shipLevels = array(4 => 1, 3 => 2, 2 => 3, 1 => 4);
	foreach ($shipLevels as $k=>$v){ # цикл по всем элементам массива т.е. для 4х видов кораблей
		for ($i = 0; $i < $v; $i++){ #ицкл по количеству k - палубных кораблей(первым ставим 4-палубный)
			$setShip = false; #переменная для отслеживания установки корабля(false не установился, true установился)
			while(!($setShip)){ #пока не установлен
				$randX = rand(0,9); #поулчаем случайные координаты для постройки корабля
				$randY = rand(0,9);
				if (!(checkShipInRadius($randY, $randX, $array))){ # проверяем наличие кораблей вокруг выбранной точки
					switch (randomRoute()) { # получили направление
						case "horizontal":
							if (!(checkShipInRadius($randY, $randX + $k - 1, $array))){# проверяем точку находящуюся справа ($randY, $randX + $k - 1)
								for ($j = $randX; $j <= $randX + $k -1; $j++) # если прошла проверку то соединяем точки
									$array[$randY][$j] = $k;
								$setShip = true; # корабль установлен => setShip = true
							}
							break;
						case "vertical": #аналогично, только по вертикали
							if (!(checkShipInRadius($randY - $k + 1, $randX, $array))){
								for ($j = $randY - $k + 1; $j <= $randY; $j++)
									$array[$j][$randX] = $k;
								$setShip = true;
							}
							break;
					}	
				}
			}
		}
	}
return $array;
}
?>
