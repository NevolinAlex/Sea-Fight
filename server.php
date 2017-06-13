<?php
session_start();
require_once 'functions.php';
if (!isset($_SESSION['shipCountPlayer'])){
	$_SESSION['shipCountPlayer'] = array(4, 3, 2, 1);
	$_SESSION['shipCountEnemy'] = array(4, 3, 2, 1);
}
if (!isset($_SESSION['enemyShipsMatrix'])){
	$_SESSION['enemyShipsMatrix'] = generateEnemyShipMatrix();
}
if (!isset($_SESSION['playerShipsMatrix'])){
	$_SESSION['playerShipsMatrix'] = fillZerosMatrix();

}
if (!isset($_SESSION['gameState'])){
	$_SESSION['gameState'] = 'preparationForBattle';
	//$_SESSION['gameState'] = 'playerTurn';
}
if (!isset($_SESSION['damagedEnemyShips'])){
	$_SESSION['damagedEnemyShips'] = array(1 => array(), 2 => array(), 3 => array(), 4 => array());
	$_SESSION['damagedPlayerShips'] = array(1 => array(), 2 => array(), 3 => array(), 4 => array());
	$_SESSION['enemyTurn'] = array();
	$_SESSION['enemyTurn'][1] = array();
}
function restartGame(){
	unset($_SESSION['enemyShipsMatrix']);
	unset($_SESSION['playerShipsMatrix']);
	unset($_SESSION['gameState']);
	unset($_SESSION['shipCountPlayer']);
	unset($_SESSION['shipCountEnemy']);
	unset($_SESSION['damagedEnemyShips']);
	unset($_SESSION['damagedPlayerShips']);
	unset($_SESSION['enemyTurn']);
	session_write_close();
}
if (($_SESSION['gameState'] == 'preparationForBattle') && (isset($_POST["ship"]))){
	$currentShip = $_POST["ship"];
	$currentLength = count($currentShip);
	$isAllowed = true;
	if (($_SESSION['shipCountPlayer'][$currentLength - 1] === 0) || ($currentLength > 4)){
		echo "Fail!";
	}
	else{
		for ($i = 0; $i < $currentLength; $i++){
			$currentCell = convertToMatrixCoordinates($currentShip[$i]);
			if(checkShipInRadius($currentCell[1], $currentCell[0], $_SESSION['playerShipsMatrix'])){
				$isAllowed = false;
				echo "Fail!";
				break;
			}
		}
		if ($isAllowed){
			for ($i = 0; $i < $currentLength; $i++){
				$currentCell = convertToMatrixCoordinates($currentShip[$i]);
				$_SESSION['playerShipsMatrix'][$currentCell[1]][$currentCell[0]] = $currentLength;
			}
			$_SESSION['shipCountPlayer'][$currentLength - 1] --;
			if (array_sum($_SESSION['shipCountPlayer']) == 0){
				$_SESSION['gameState'] = 'playerTurn';
				$_SESSION['shipCountPlayer'] = array(4, 3, 2, 1);
				echo "Player turn";
			}
			else echo "Success!";
		}
	}
}
if (($_SESSION['gameState'] == 'playerTurn') && (isset($_POST["shot"]))){
	$currentIndexOfShot = $_POST["shot"];
	$currentCell = convertToMatrixCoordinates($currentIndexOfShot);
	if (($_SESSION['enemyShipsMatrix'][$currentCell[1]][$currentCell[0]] === "missed") ||
		(preg_match("/^hit([1-9])/", $_SESSION['enemyShipsMatrix'][$currentCell[1]][$currentCell[0]])) ||
		($_SESSION['enemyShipsMatrix'][$currentCell[1]][$currentCell[0]] === "died")
		){
		echo json_encode("retry");
	}
	else if ($_SESSION['enemyShipsMatrix'][$currentCell[1]][$currentCell[0]] === 0){
		$_SESSION['enemyShipsMatrix'][$currentCell[1]][$currentCell[0]] = "missed";
		$array = array("miss", convertToIndex($currentCell[1], $currentCell[0]));
		$echoArray = array_merge($array, enemyTurn());
		echo json_encode($echoArray);
	}
	else {
		$length = $_SESSION['enemyShipsMatrix'][$currentCell[1]][$currentCell[0]];
		array_push($_SESSION['damagedEnemyShips'][$length], $currentIndexOfShot);
		$_SESSION['enemyShipsMatrix'][$currentCell[1]][$currentCell[0]] = "hit$length";
		if (count($_SESSION['damagedEnemyShips'][$length]) == $length){
			$_SESSION['shipCountEnemy'][$length - 1] --;
			$missArray = array();
			array_push($missArray, "die", $currentIndexOfShot);
			for ($i = 0; $i < $length; $i++){
				$currentArray = getMissArray($_SESSION['damagedEnemyShips'][$length][$i], 'enemyShipsMatrix');
				$missArray = array_merge($missArray, $currentArray);
			}
			$_SESSION['damagedEnemyShips'][$length] = array();
			if (array_sum($_SESSION['shipCountEnemy']) == 0){
				array_push($missArray, "player");
			}
			else 
				array_push($missArray, "none");
			echo json_encode($missArray);
		}else {
			$array = array("hit", convertToIndex($currentCell[1], $currentCell[0]));
			echo json_encode($array);
		}
	}
	
	
	
	//echo json_encode($currentCell[1]);
	
}
function enemyTurn(){
	$array1 = array();
	while(1){
		if (count($_SESSION['enemyTurn'][1]) === 0){
			$randX = rand(0, 9);
			$randY = rand(0, 9);
		}
		else{
			$currentIndex = convertToMatrixCoordinates($_SESSION['enemyTurn'][1][count($_SESSION['enemyTurn'][1]) - 1]);
			$randX = $currentIndex[0];
			$randY = $currentIndex[1];
		}
		$indexCell = convertToIndex($randY, $randX);
		if ($_SESSION['playerShipsMatrix'][$randY][$randX] === 0){
			$_SESSION['playerShipsMatrix'][$randY][$randX] = "missed";
			array_push($array1, "missed", convertToIndex($randY, $randX));
			array_pop($_SESSION['enemyTurn'][1]);
			return $array1;
		}
		else if (($_SESSION['playerShipsMatrix'][$randY][$randX] < 5) && ($_SESSION['playerShipsMatrix'][$randY][$randX] > 0)){
			$length = $_SESSION['playerShipsMatrix'][$randY][$randX];
			array_push($_SESSION['damagedPlayerShips'][$length], convertToIndex($randY, $randX));

			if (count($_SESSION['damagedPlayerShips'][$length]) == $length){
				$_SESSION['shipCountPlayer'][$length - 1] --;
				array_push($array1, "died");
				$diedLength = count($array1);
				$array1[$diedLength] = array();
				//$array[count($array)] = array();
				array_push($array1[$diedLength], convertToIndex($randY, $randX));
				for($i = 0; $i < $length; $i++){
					$currentArray = getMissArray($_SESSION['damagedPlayerShips'][$length][$i], 'playerShipsMatrix');
					$array1[$diedLength] = array_merge($array1[$diedLength], $currentArray);
				}
				if (array_sum($_SESSION['shipCountPlayer']) == 0){
					array_push($array1, "enemy");
					return $array1;
				}	

				$_SESSION['enemyTurn'][1] = array();
				$_SESSION['damagedPlayerShips'][$length] = array();
			}
			else {
				array_push($array1, "hit", convertToIndex($randY, $randX));
				$_SESSION['playerShipsMatrix'][$randY][$randX] = "hit$length";
				if (count($_SESSION['enemyTurn'][1]) == 0){
					$_SESSION['enemyTurn'][0] = $indexCell;
					array_push($_SESSION['enemyTurn'][1], $indexCell - 1, $indexCell + 11, $indexCell +1, $indexCell - 11);
				}
				else {
					array_pop($_SESSION['enemyTurn'][1]);
					if ((($_SESSION['enemyTurn'][0] - $indexCell) % 11) == 0)
						$mod = 11;
					else $mod = 1;
					
					if (($_SESSION['enemyTurn'][0] - $indexCell) < 0)
						array_push($_SESSION['enemyTurn'][1], $indexCell + $mod);
					else array_push($_SESSION['enemyTurn'][1], $indexCell - $mod);
				}

				
			}
		}
		else {
			array_pop($_SESSION['enemyTurn'][1]);
		}
	}
	return $array;
}
if (isset($_POST["clear"])){
	if ($_POST["clear"]){
		restartGame();
		echo "successfully!";
	}
}
?>