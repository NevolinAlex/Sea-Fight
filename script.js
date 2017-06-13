var matrixPlayer;
var matrixEnemy;
var columns = 10;
var rows = 10;
var n = 121;
var move = false;
var currentDirection = "horizontal";
var lastDirection = "horizontal";
var numberOfShips = [];
var maxLengthOfCurrentShip;
var currentShip = [];
var indexOfStartCell;
//var gameState = "playerTurn";
var gameState = "preparationForBattle";
var startCell = {
	X:0,
	Y:0
};
var currentCell = {
	X: 0,
	Y: 0
};

function run () {
	var scrollin = document.getElementById("battleLog");
	   setInterval(function() {         
        scrollin.scrollTop = 9999;
		}, 500);

	matrixPlayer = document.getElementById("matrix");
	matrixEnemy = document.getElementById("matrixEnemy");
	CreateMatrixPlayer();
	CreateMatrixEnemy();
	matrixPlayer.addEventListener("mousedown", StartDrag, false);
	matrixPlayer.addEventListener("mousemove", Drag, false);
	matrixPlayer.addEventListener("mouseup", StopDrag, false);
	matrixEnemy.addEventListener("mousedown", mouseDownEnemy, false);
	addTextToBattleLog("Игра началась!\nРасставляейте корабли.");
	$('#but').on('click', function(){
		$.post("server.php",
		{'clear': true},
		function(data){
			for (var i = 0; i < n; i++){
				if ((i > 11) && (i % 11) != 0){
				setDefaultColor(i, matrixPlayer);
				setDefaultColor(i, matrixEnemy);
				}
				setBackgroundImage(i, false, "putShip", matrixPlayer);
				setBackgroundImage(i, false, "putShip", matrixEnemy);	
			}
			gameState = "preparationForBattle";
			$('#battleLog').html("		Игра началась!\nРасставляейте корабли. \n");
			alert("Cleaning " + data);
		}
		);
	});
	

}
function sleep(ms) {
	ms += new Date().getTime();
	while (new Date() < ms){}
} 
function addTextToBattleLog(text){
	$('#battleLog').html($('#battleLog').html() + text + "\n");
}
function BorderCheck(index){
	if (((index % 11) == 0) || (index < 11))
		return true;
}
function CreateMatrixPlayer(){
	for(var i=0; i<n; i++){
		var div = document.createElement('div');
		div.className = 'cell';
		matrixPlayer.appendChild(div);	
	}
	CreateBorder(matrixPlayer);

}
function ConvertToMatrixCoordinates(index){
	index = index - Math.floor(index / 11) - 11;
	var array = [index % 10,Math.floor(index / 10)];
	return array;
}
function CreateBorder(player){
	var k = 0;
	for (var i = 0; i < n; i++){
		if (BorderCheck(i))
			setColor(i, "#9932CC", player);
		if ((i < 11) && (i > 0))
			player.getElementsByClassName("cell")[i].innerHTML = i;
		if (((i % 11) == 0) && (i != 0)){
			player.getElementsByClassName("cell")[i].innerHTML = String.fromCharCode(k+65);
			k++;
		}
	}
}
function CreateMatrixEnemy(){
	for(var i=0; i<n; i++){
		var div = document.createElement('div');
		div.className = 'cell';	
		matrixEnemy.appendChild(div);
	}
	CreateBorder(matrixEnemy);
}

function setBackgroundImage(cellIndex, value, type, player){
	var cell = player.children[cellIndex];
	if (value){
		if (type == "putShip")
			cell.style.backgroundImage = "url('mayflower-4.jpg')";
		else cell.style.backgroundImage = "url('burn.jpg')";
	}
	else
		cell.style.backgroundImage = "none";
}

function setColor(cellIndex, color, player){
	var cell = player.children[cellIndex];
	cell.style.backgroundColor = color
}

function setDefaultColor(cellIndex, player){
	var cell = player.children[cellIndex];
	cell.style.backgroundColor = "transparent";
}

function popToIndex(cellIndex){
	while(currentShip[currentShip.length - 1] != cellIndex){
		setDefaultColor(currentShip[currentShip.length - 1], matrixPlayer);
		currentShip.pop();
	}	
}
function popAll(){
	while (currentShip.length != 0){
		setDefaultColor(currentShip[currentShip.length - 1],matrixPlayer);
		currentShip.pop();
	}
}
function isChangeDirection(){
	if (lastDirection == currentDirection)
		return false
	return true;
}
function getCurrentDirection(curY, curX){
	if (curX == startCell.X)
		currentDirection = "vertical";
	else
		currentDirection = "horizontal";
}

function StartDrag(event){
	move = true;
	evt = event.target || event.srcElement;
	var parents = evt.parentNode;
	var indexOfCurrentCell = Array.prototype.indexOf.call(parents.children, evt);
	indexOfStartCell = indexOfCurrentCell;
	if ((!BorderCheck(indexOfCurrentCell)) && gameState == "preparationForBattle"){
		var newIndex = [] = ConvertToMatrixCoordinates(indexOfCurrentCell);
		startCell.X = newIndex[0];
		startCell.Y = newIndex[1];
		setColor(indexOfCurrentCell, "#FF00FF", matrixPlayer);
		currentShip.push(indexOfCurrentCell);
	}	
}


function Drag(event){
	if (move){
		evt = event.target || event.srcElement;
		var parents = evt.parentNode;
		var indexOfCurrentCell = Array.prototype.indexOf.call(parents.children, evt);
		if ((!BorderCheck(indexOfCurrentCell)) && gameState == "preparationForBattle"){
			var newIndex = [] = ConvertToMatrixCoordinates(indexOfCurrentCell);
			currentCell.X = newIndex[0];
			currentCell.Y = newIndex[1];
			if ((startCell.Y == currentCell.Y) || (startCell.X == currentCell.X) && (startCell != currentCell)){
				getCurrentDirection(currentCell.Y, currentCell.X);
				if (!isChangeDirection()){
					if (currentShip.indexOf(indexOfCurrentCell) == -1){
						currentShip.push(indexOfCurrentCell);
						setColor(indexOfCurrentCell, "#FF00FF", matrixPlayer);
					}
					else if (indexOfCurrentCell != currentShip[currentShip.length - 1]){
						popToIndex(indexOfCurrentCell);
					}
				}
				else{
					popToIndex(indexOfStartCell);
					var step;
					var curStep;
					if (currentDirection == "horizontal")
						step = Math.sign(indexOfCurrentCell - indexOfStartCell);
					else 
						step = Math.sign(indexOfCurrentCell - indexOfStartCell) * 11;
					curStep = step;

					while (currentShip.indexOf(indexOfCurrentCell) == -1){
						currentShip.push(indexOfStartCell + curStep);
						setColor(indexOfStartCell + curStep, "#FF00FF", matrixPlayer);
						curStep += step;
					}
					lastDirection = currentDirection;
				}
			}
			
		}
	}
}
function StopDrag(event){
	move = false;
	evt = event.target || event.srcElement;
	var parents = evt.parentNode;
	var indexOfCurrentCell = Array.prototype.indexOf.call(parents.children, evt);
	if (!BorderCheck(indexOfCurrentCell)){
	}
	if (gameState == "preparationForBattle"){
		$.post("server.php",
		{'ship[]': currentShip},
		function(data){
			if (data == "Fail!"){
				addTextToBattleLog("Установите корабль правильно!");
				popAll();
			}
			else {
				for(var i = 0; i < currentShip.length; i++)
					setBackgroundImage(currentShip[i], true, "putShip", matrixPlayer);
				addTextToBattleLog("Вы установили " + currentShip.length + "-палубный корабль.");
				currentShip = [];
				if (data == "Player turn"){
					gameState = "playerTurn";
					addTextToBattleLog("Игра началась, ваш ход!");
				}
			}
		}
		);
	}
}
function mouseDownEnemy(event){
	evt = event.target || event.srcElement;
	var parents = evt.parentNode;
	var indexOfCurrentCell = Array.prototype.indexOf.call(parents.children, evt);
	if (!BorderCheck(indexOfCurrentCell) && gameState == "playerTurn"){
		$.post("server.php",
		{'shot': indexOfCurrentCell},
		function(data){ 
			var dt = JSON.parse(data);
			if (dt[0] === "miss"){
				gameState = "enemy";
				addTextToBattleLog("Вы промахнулись. \nХод противника:");
				setColor(dt[1], "#00FFFF", matrixEnemy);
				for (var i = 2; i < dt.length; i+=2){
					if (dt[i] == "missed"){
						addTextToBattleLog("Враг промахнулся.");
						setColor(dt[i+1], "#00FFFF", matrixPlayer);
					}
					if (dt[i] == "hit"){
						addTextToBattleLog("Ваш корабль ранен.");
						setBackgroundImage(dt[i+1], true, "burn", matrixPlayer);
					}
					if (dt[i] == "died"){
						addTextToBattleLog("Ваш корабль потоплен.")
						setBackgroundImage(dt[i+1][0], true, "burn", matrixPlayer);
						for (var j = 1; j < dt[i+1].length; j++){
							setColor(dt[i+1][j], "#00FFFF", matrixPlayer);
						}
					}
				}
				gameState = "playerTurn";
				if (dt[dt.length - 1] == "enemy"){
					gameState = "end";
					alert("Enemy win!");
				}
				addTextToBattleLog("Ваш ход.");
			} else if (dt[0] === "hit"){
				addTextToBattleLog("Корабль противника ранен.")
				setBackgroundImage(dt[1], true, "burn", matrixEnemy);
			}
			else if (dt[0] = "die"){
				addTextToBattleLog("Корабль противника потоплен.")
				setBackgroundImage(dt[1], true, "burn", matrixEnemy);
				for (var i = 2; i < dt.length - 1; i++){
				setColor(dt[i], "#00FFFF", matrixEnemy);
				}
				if (dt[dt.length - 1] == "player"){
					gameState = "end";
					alert("You win!");
				}
			}
		}
		);
	}
}









