<?php 
session_start();
$time_start = microtime(true);
if (empty($_SESSION['timezone'])) {
	date_default_timezone_set('Europe/Moscow');
} else {
	date_default_timezone_set($_SESSION['timezone']);
}


function filter(string $str) {
	return trim(stripslashes(htmlspecialchars($str)));
}

function sanitize(string $value, string $regexp) {
	if (preg_match($regexp, $value) === 0) {
		$value = "";
	}
	return $value;
}

function write_error(string $msg) {
	$_SESSION['error_msg'] = $msg;
}

function write_error_messages(string $string_for_one_error, string $string_for_many_errors, $args) {
	$string_arr = [];
	foreach ($args as $key => $value) {
		if (empty($value) && ($value !== '0') && ($value !== '0.0') && ($value !== '0,0')) {
			$string_arr[] = $key;
		}
	}
	$string_arr_count = count($string_arr);
	if ($string_arr_count == 1) {
		write_error($string_for_one_error . $string_arr[0]);
		return;
	}
	$string_err = $string_for_many_errors;
	for ($i=0; $i < $string_arr_count; $i++) {
		if ($i > 0) {
			$string_err .= ",";
		}
		$string_err .= " " . $string_arr[$i];
	}
	write_error($string_err);
}

function calc(float $r, float $x, float $y) {
	return ((($x >= 0) && ($y >= 0) && (pow($x, 2) + pow($y, 2) <= pow($r, 2))) || 
		(($x > 0) && ($y < 0) && ($y >= 0.5*($x - $r))) ||
		(($x < 0) && ($y < 0) && ($x >= -$r) && ($y >= -($r / 2))));
}

function write_results(string $message, float $time_start, $cords) {
	if (empty($_SESSION['results_arr'])) {
		$_SESSION['results_arr'] = [[$message, microtime(true) - $time_start, $cords, date("H:i:s")]];
	} else {
		array_unshift($_SESSION['results_arr'], [$message, microtime(true) - $time_start, $cords, date("H:i:s")]);
	}
}

function handling_post(float $time_start) {
	$count_post = count($_POST);
	if ($count_post != 0) {
		if ($count_post == 3) {
			$r = filter($_POST['r']);
			$x = filter($_POST['x']);
			$y = filter($_POST['y']);
			if ((!empty($r) || $r === '0' || $r === '0.0' || $r === '0,0') && 
				(!empty($x) || $x === '0' || $x === '0.0' || $x === '0,0') && 
				(!empty($y) || $y === '0' || $y === '0.0' || $y === '0,0')) {
				$regexp = '/^[-+]?[0-9]*(?:[.,][0-9]+)*$/';
				if (preg_match($regexp, $r) > 0 && 
					preg_match($regexp, $x) > 0 && 
					preg_match($regexp, $y) > 0) {
					$r = (float) $r;
					$x = (float) $x;
					$y = (float) $y;
					if (($r >= 1.0 && $r <= 3) && ($x >= -2 && $x <= 2) && ($y >= -5 && $y <= 3)) {
						if (calc($r, $x, $y)) {
							write_results("точка попала", $time_start, [$x, $y, $r]);
						} else {
							write_results("точка не попала", $time_start, [$x, $y, $r]);
						}
					} else {
						if ($r < 1.0 || $r > 3.0) {
							$r = "";
						} 
						if ($x < -2.0 || $x > 2.0) {
							$x = "";
						}
						if ($y < -5.0 || $y > 3.0) {
							$y = "";
						}
						write_error_messages(
							"Значение выходит за допустимый диапазон: ", 
							"Значения выходят за допустимый диапазон:", 
							['R' => $r, 'X' => $x, 'Y' => $y]
						);
					}
				} else {
					write_error_messages(
						"Неправильный формат: ", 
						"Неправильный формат:", 
						[
							'R' => sanitize($r, $regexp), 
							'X' => sanitize($x, $regexp), 
							'Y' => sanitize($y, $regexp)
						]
					);
				}
			} else {
				write_error_messages(
					"Передан пустой аргумент: ", 
					"Переданы пустые аргументы:", 
					['R' => $r, 'X' => $x, 'Y' => $y]
				);
			}
		} else {
			write_error("Передано неправильное количество аргументов");
		}
	}
}
handling_post($time_start);
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<title>первая лаба</title>

	<!--[if lt IE 9]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
<style type="text/css">
* {
   box-sizing: border-box;
}
body,
tbody {
	margin: 0px;
	padding: 0px;
}
body {
	background: #FFF;
	font-family: 'Roboto', sans-serif;
}
p {
	margin: 16px 10px 20px;
}
h1, h2, h3, h4 {
	color: #4A90E2;
	font-weight: bold;
}
a {
	color: darkblue;
	outline: none;
	text-decoration: underline;
}
a:hover {
	text-decoration: none;
}
label {
	display: inline-block;
 	margin-left: 10px;
 	margin-bottom: 5px;
 	color: #9D959D;
	font-size: 20px;
	font-weight: 300;
}
#pole_size {
	width: 100%;
}
#header {
	height: 80px; 
	background-color: #EEE; 
	padding: 32px; 
	text-align: center;
}
#left_col {
	width: 40%;
}
#canvas {
	margin-top: 5%;
	margin-left: 5%;
	padding: 0px;
	display: inline-block;
	border: 1px solid #000;
}
.column {
	width: 200px;
	vertical-align:top;
	background-color: #FFF; 
}
.wrapper {
	height: 100%;
	position: relative;
}

.ui-form {
	max-width: 450px;
	padding: 80px 30px 30px;
	margin: 50px auto 30px;
	background: #FFF;
	border: 3px solid #DDD;
}
.ui-form h3 {
	position: relative;
	z-index: 5;
	margin: 0 0 60px;
	text-align: center;
	color: #4A90E2;
	font-size: 30px;
	font-weight: normal;
}
.form-row {
	position: relative;
	margin-bottom: 40px;
}
.form-row input {
	display: block;
	width: 100%;
	padding: 0 10px;
	line-height: 40px;
	font-family: 'Roboto', sans-serif;
	background: none;
	border-width: 0;
	border-bottom: 2px solid #4A90E2;
	transition: all 0.2s ease;
}
.form-row .text-input-label {
	position: absolute;
	margin: 0px;
	left: 13px;
	color: #9D959D;
	font-size: 20px;
	font-weight: 300;
	transform: translateY(-35px);
	transition: all 0.2s ease;
}
.form-row input[type="text"]:focus {
	outline: 0;
	border-color: #F77A52;
}
.form-row input[type="text"]:focus+label, 
.form-row input[type="text"]:valid+label {
	margin-left: -20px;
	font-size: 14px;
	font-weight: 400;
	outline: 0;
	border-color: #F77A52;
	color: #F77A52;
}
.ui-form input[type="submit"] {
	width: 100%;
	padding: 0;
	line-height: 42px;
	background: #4A90E2;
	border-width: 0;
	color: #FFF;
	font-size: 20px;
}
.ui-form p {
   margin: 0;
   padding-top: 10px;
}
.error {
	color: #CC0000;
	font-size: 20px;
	font-weight: 300;
	text-align: center;
}

fieldset {
	margin-bottom: 20px;
	border: 3px solid #DDE;
}
fieldset label{
	display: block;
	margin-left: 0px;
}
fieldset input {
 	appearance: none;
 	border-radius: 50%;
 	width: 16px;
 	height: 16px;
 	border: 2px solid #4A90E2;
 	transition: 0.2s all linear;
 	outline: none;
 	margin-right: 5px;
 	position: relative;
 	top: 4px;
}
fieldset input:checked {
 	border: 4px solid #F77A52;
}

.select { 
	display: block; 
	font-size: 16px; 
	font-family: sans-serif; 
	font-weight: 700; 
	color: #444; 
	line-height: 1.3;
	padding: 5px; 
	width: 100%; 
	max-width: 100%; 
	margin: 0;
	border: 3px solid #DDD;
	appearance: none;
	background-color: #FFF; 
} 
.select:focus { 
	border-color: #F77A52; 
	color: #222;
	outline: none; 
} 
.select option { 
	font-weight:normal; 
}

#results {
	width: 100%;
	padding: 5px;
	background-color: #EEE;
	border: none;
}
#results tr {
	border: none;
}
.response {
	display: inline-block;
	width: 100%;
	padding: 5px;
	margin: 10px 0px;
	background-color: #FFF;
}
.response td {
	display: inline-block;
	padding: 5px;
	border-right: 1px solid #EEE;
	color: #EEE;
	font-size: 18px;
	font-weight: 300;
}
.response td:last-child,
.response.triple-column td.per-last {
	border-right: none;
}
.response.single-column td {
	width: 100%;
}
.response.double-column td {
	width: 49%;
}
.response.triple-column td {
	width: 32%;
}
.response.triple-column td.last {
	width: 100%;
	border-top: 1px solid #EEE;
}
.response.neutral {
	background-color: #CCC;
}
.response.success {
	background-color: #339966;
}
.response.fail {
	background-color: #CC0000;
}
.response.neutral td {
	color: #000;
}
</style>
</head>
<body>
	<table id="pole_size">
		<tbody>
			<tr id="header">
				<td><h2>Стрельбицкий Илья</h2></td>
				<td colspan="2"><h3>Группа P32101</h3><h4>1918 вариант</h4></td>
			</tr>
			<tr class="wrapper">
				<td id="left_col" class="column">
					<canvas id="canvas" height="600" width="600" <?php $results_arr = $_SESSION['results_arr'];
					if (empty($results_arr)) {
						echo('x="0" y="0" r="0"');
					} else {
						$result = $results_arr[0];
						if (count($result) != 4) {
							echo('x="0" y="0" r="0"');
						} else {
							$cords = $result[2];
							echo('x="' . $cords[0] . '" y="' . $cords[1] . '" r="' . $cords[2] . '"');
						}
					} ?>></canvas>
				</td>
				<td id="center_col" class="column">
					<form action="index.php" method="POST" class="ui-form" id="dot-form">
						<h3>Проверка попадания точки</h3>
							<fieldset id="x">
								<label>X:</label>
								<input type="radio" value="-2" name="x" checked>-2
								<input type="radio" value="-1.5" name="x">-1.5
								<input type="radio" value="-1" name="x">-1
								<input type="radio" value="-0.5" name="x">-0.5
								<input type="radio" value="0" name="x">0
								<input type="radio" value="0.5" name="x">0.5
								<input type="radio" value="1" name="x">1
								<input type="radio" value="1.5" name="x">1.5
								<input type="radio" value="2" name="x">2
							</fieldset>
						<div class="form-row">
							<input type="text" id="y" name="y" required autocomplete="off"><label for="y" class="text-input-label">Y:</label>
						</div>
						<div class="form-row">
							<label for="r">R:</label>
							<select id="r" name="r" size="3" class="select">
					    		<option selected value="1">1</option>
					    		<option value="1.5">1.5</option>
					    		<option value="2">2</option>
					    		<option value="2.5">2.5</option>
					    		<option value="3">3</option>
							</select>
						</div>
						<p><input type="submit" value="отправить"></p>
						<?php if (!empty($_SESSION['error_msg'])): ?>
						<p id="form-error-server" class="error"><?php echo($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?></p>
						<?php endif; ?>
						<p id="form-error" class="error"></p>
					</form>
				</td>
				<td id="right_col" class="column">
					<h2>Результаты проверки</h2>
					<table id="results">
						<tbody>
							<?php if (empty($_SESSION['results_arr'])): ?>
							<tr class="response neutral single-column"><td colspan="3">Пока здесь пусто</td></tr>
							<?php else: ?>
							<?php foreach ($_SESSION['results_arr'] as $num_result => $result): ?>
									<?php if ($result[0] === 'точка попала'): ?>
										<tr id="last-response" class="response success triple-column">
											<td><?php if ($num_result == 0) {echo('Последний ответ - ');} ?>точка попала</td>
									<?php else: ?>
										<tr class="response fail triple-column">
											<td><?php if ($num_result == 0) {echo('Последний ответ - ');} ?>точка не попала</td>
									<?php endif; ?>
											<td>Время работы скрипта <?php echo(number_format($result[1], 9, '.', '')) *1000; ?>ms</td>
											<td class=per-last>Текущее время <?php echo($result[3]); ?></td>
											<td class="last">Аргументы: <?php echo('x: ' . $result[2][0] . '; y:' . $result[2][1] . '; r: ' . $result[2][2]); ?></td>
										</tr>
							<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
</body>
<script>
function writeError(element, text) {
	if (typeof element.innerText !== 'undefined') {
		// IE8
		element.innerText = text;
	} else {
		// Остальные браузеры
		element.textContent = text;
	}
}

function writeErrorMessages(elementError, stringForOneError, stringForManyErrors, args) {
	let stringArr = [];
	for (let key in args) {
		if (args[key] == '' || args[key] == null) {
			stringArr.push(key);
		}
	}
	let stringArrCount = stringArr.length;
	if (stringArrCount == 1) {
		writeError(elementError, stringForOneError + stringArr[0]);
		return;
	}
	let stringErr = stringForManyErrors;
	for (let i=0; i < stringArrCount; i++) {
		if (i > 0) {
			stringErr += ",";
		}
		stringErr += " " + stringArr[i];
	}
	writeError(elementError, stringErr);
}

function findValueFromFieldset(fieldset) {
	for (let i=0; i < fieldset.elements.length - 1; i++) {
		let input = fieldset.elements[i];
		if (input.checked) {
			return input.value.trim();
		}
	}
	return '';
}

function filterForm(fieldX, fieldY, fieldR, formError) {
	let valueX = findValueFromFieldset(fieldX);
	let valueY = fieldY.value.trim();
	let valueR = fieldR.value.trim();

	if (valueX == '' || valueY == '' || valueR == '') {
		writeErrorMessages(
			formError,
			'Передан пустой аргумент: ', 
			'Переданы пустые аргументы:', 
			{'X': valueX, 'Y': valueY, 'R': valueR}
		);
		return false;
	}

	let regex = '^[-+]?[0-9]{0,9}(?:[.,][0-9]{1,9})*$';
	let resultX = valueX.match(regex);
	let resultY = valueY.match(regex);
	let resultR = valueR.match(regex);

	if (resultX == null || resultY == null || resultR == null) {
		writeErrorMessages(
			formError,
			'Неправильный формат аргумента: ', 
			'Неправильный формат аргументов:', 
			{'X': resultX, 'Y': resultY, 'R': resultR}
		);
		return false;
	}

	valueX = parseFloat(valueX);
	valueY = parseFloat(valueY);
	valueR = parseFloat(valueR);
	if ((valueX < -2.0 || valueX > 2.0) || 
		(valueY < -5.0 || valueY > 3.0) || 
		(valueR < 1.0 || valueR > 3.0)) {
		if (valueX < -2.0 || valueX > 2.0) {
			valueX = '';
		} 
		if (valueY < -5.0 || valueY > 3.0) {
			valueY = '';
		} 
		if (valueR < 1.0 || valueR > 3.0) {
			valueR = '';
		}
		writeErrorMessages(
			formError,
			'Значение выходит за допустимый диапазон: ', 
			'Значения выходят за допустимый диапазон:', 
			{'X': valueX, 'Y': valueY, 'R': valueR}
		);
		return false;
	}
	return true;
}

const form = document.getElementById('dot-form');
const fieldX = document.getElementById('x');
const fieldY = document.getElementById('y');
const fieldR = document.getElementById('r');
const formError = document.getElementById('form-error');

form.addEventListener('submit', (e) => {
	e.preventDefault();
	if (filterForm(fieldX, fieldY, fieldR, formError)) {
		form.submit();
	}
});


const canvas = document.getElementById('canvas');
const canvasObj = {
	width: canvas.width,
	height: canvas.height,
	font: "16px serif",
	center: {x: 0, y: 0},
	dotArgs: {x: 0, y: 0, r: 0},
	step: {x: 17, y: 17},
	serif: {
		numSerif: {x: 2, y: 2},
		numStepForSerif: {x: 3, y: 3}
	},
	r: {},
	lineWidth: 1,
};
canvasObj.r = {
	step: {
		x: canvasObj.serif.numStepForSerif.x * canvasObj.step.x, 
		y: canvasObj.serif.numStepForSerif.y * canvasObj.step.y
	}
}

function getArgsForGraph(canvas) {
	let x = canvas.getAttribute('x');
	let y = canvas.getAttribute('y');
	let r = canvas.getAttribute('r');

	if (x == null || y == null || r == null) {
		return null;
	}
	return {'x': x, 'y': y, 'r': r};
}

function findCenter(canvasObj) {
	canvasObj.center.x = Math.round(canvasObj.width / canvasObj.step.x / 2) * canvasObj.step.x;
	canvasObj.center.y = Math.round(canvasObj.height / canvasObj.step.y / 2) * canvasObj.step.y;
}

function drawArea(ctx, canvasObj, color) {
	const center = canvasObj.center;
	const r = canvasObj.r;

	ctx.beginPath();
	ctx.moveTo(center.x, center.y);
	ctx.arc(center.x, center.y, r.step.x * 2, Math.PI + Math.PI / 2, Math.PI * 2);
	ctx.lineTo(center.x - (r.step.x * 2), center.y);
	ctx.lineTo(center.x - (r.step.x * 2), center.y + r.step.y);
	ctx.lineTo(center.x, center.y + r.step.y);
	ctx.lineTo(center.x + (r.step.x * 2), center.y);
	ctx.lineTo(center.x, center.y);

	ctx.fillStyle = color;
   ctx.fill();
}

function drawGrid(ctx, canvasObj, color){
	const stepX = canvasObj.step.x;
	const stepY = canvasObj.step.y;
	ctx.beginPath();
   for(let i = 1 + stepX; i < canvasObj.width; i += stepX){
      ctx.moveTo(i, 0);
      ctx.lineTo(i, canvasObj.height);
   }
   for(let j = 1 + stepY; j < canvasObj.height; j += stepY){
      ctx.moveTo(0, j);
      ctx.lineTo(canvasObj.width, j);
   }
   ctx.strokeStyle = color;
   ctx.lineWidth = canvasObj.lineWidth;
   ctx.stroke();
}

function drawAxes(ctx, canvasObj, color) {
	const center = canvasObj.center;
	const step = canvasObj.step;

	ctx.strokeStyle = color;
	ctx.fillStyle = color;
   ctx.lineWidth = canvasObj.lineWidth;
	//ось X
	ctx.beginPath();
	ctx.moveTo(0, center.y);
	ctx.lineTo(canvasObj.width, center.y);
	//ось Y
	ctx.moveTo(center.x, canvasObj.height);
	ctx.lineTo(center.x, 0);
   ctx.stroke();

   //отрисовка стрелок
   const halfStepX = Math.round(step.x / 2);
   const halfStepY = Math.round(step.y / 2);
   //для X
   ctx.beginPath();
   ctx.moveTo(canvasObj.width, center.y);
   ctx.lineTo(canvasObj.width - halfStepX, center.y + halfStepY);
   ctx.lineTo(canvasObj.width - halfStepX, center.y - halfStepY);
   ctx.fill();
   //для Y
   ctx.beginPath();
   ctx.moveTo(center.x - halfStepX, halfStepY);
   ctx.lineTo(center.x, 0);
   ctx.lineTo(center.x + halfStepX, halfStepY);
   ctx.fill();
}

function drawSerifs(ctx, canvasObj, color) {
	const center = canvasObj.center;
	const step = canvasObj.step;
	const serif = canvasObj.serif;
	const r = canvasObj.r;
	const startSerifX = center.x - (r.step.x * serif.numSerif.x);
	const startSerifY = center.y - (r.step.y * serif.numSerif.y);

	ctx.beginPath();
	// Рисуем для оси X
	for (let i=0; i < serif.numSerif.x * 2 + 1; i++) {
		ctx.moveTo(startSerifX + (r.step.x * i), center.y - Math.round(step.y / 2));
		ctx.lineTo(startSerifX + (r.step.x * i), center.y + Math.round(step.y / 2));
	}
	// Рисуем для оси Y
	for (let i=0; i < serif.numSerif.y * 2 + 1; i++) {
		ctx.moveTo(center.x - Math.round(step.x / 2), startSerifY + (r.step.y * i));
		ctx.lineTo(center.x + Math.round(step.x / 2), startSerifY + (r.step.y * i));
	}
	ctx.strokeStyle = color;
   ctx.lineWidth = canvasObj.lineWidth;
	ctx.stroke();
}

function drawLabels(ctx, canvasObj, color) {
	const center = canvasObj.center;
	const step = canvasObj.step;
	const serif = canvasObj.serif;
	const r = canvasObj.r;

	ctx.font = canvasObj.font;
	// Для оси X
	ctx.strokeText('-R', center.x - (r.step.x * 2) - Math.round(step.x / 2), center.y - step.y);
	ctx.strokeText('-R/2', center.x - r.step.x - Math.round(step.x / 2), center.y - step.y);
	ctx.strokeText('R/2', center.x + r.step.x - Math.round(step.x / 2), center.y - step.y);
	ctx.strokeText('R', center.x + (r.step.x * 2) - Math.round(step.x / 4), center.y - step.y);
	ctx.strokeText('x', canvasObj.width - Math.round(step.x * 0.6), center.y - step.y);
	// Для оси Y
	ctx.strokeText('-R', center.x + step.x, center.y + (r.step.y * 2) + Math.round(step.y / 4));
	ctx.strokeText('-R/2', center.x + step.x, center.y + r.step.y + Math.round(step.y / 4));
	ctx.strokeText('R/2', center.x + step.x, center.y - r.step.y + Math.round(step.y / 4));
	ctx.strokeText('R', center.x + step.x, center.y - (r.step.y * 2) + Math.round(step.y / 4));
	ctx.strokeText('y', center.x + step.x, Math.round(step.y * 0.6));
}

function drawDot(ctx, canvasObj, color) {
	const args = getArgsForGraph(canvas);
	if (args == null) {
		return;
	}
	const x = Number.parseFloat(args['x']);
	const y = Number.parseFloat(args['y']);
	const r = Number.parseFloat(args['r']);
	const center = canvasObj.center;
	const stepR = canvasObj.r.step;
	ctx.beginPath();
	ctx.arc(
		center.x + ((x / r) * stepR.x * 2), 
		center.y - ((y / r) * stepR.y * 2), 
		Math.round(canvasObj.step.x / 4),
		0, 
		Math.PI * 2
	);
	ctx.fillStyle = color;
   ctx.fill();
}

function drawCanvas(canvas, canvasObj) {
	const ctx = canvas.getContext('2d');
	findCenter(canvasObj);
	drawArea(ctx, canvasObj, '#4A90E2');
   drawGrid(ctx, canvasObj, 'lightgray');
   drawAxes(ctx, canvasObj, 'black');
   drawSerifs(ctx, canvasObj, 'black');
   drawLabels(ctx, canvasObj, 'black');
   drawDot(ctx, canvasObj, '#F77A52');
}
drawCanvas(canvas, canvasObj);
</script>
</html>
