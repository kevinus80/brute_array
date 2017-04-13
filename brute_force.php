<?php

function start_time() {
    $m = explode(' ', microtime(false));
    return $m[0] + $m[1];
}

function end_time($start_time) {
    $m = explode(' ', microtime(false));
    return ($m[0] + $m[1]) - $start_time;
}

// Создадим массив с 10 миллионами элементов
$start_time = start_time();

$array = array();
for ($i=1; $i <= 10000000; $i++){
    $array[] = $i;
}

$end_time = end_time($start_time);
echo "Создание массива заняло: {$end_time} sec \n--------------\n";

$start_time = start_time();

// найдем в массиве элемент ближе к концу самого массива обычным перебором,
// дабы оценить саму скорость работы перебора всего массива поэлементно
$findValue = 10000000;
$foundMessage = "Not found \n";
foreach ($array as $key => $value){
    if ($value == $findValue) {
        $foundMessage = "FOUND! key is {$key} \n";
        break;
    }
}
echo $foundMessage;


$end_time1 = end_time($start_time);
echo "Чистое время на поиск элемента составил {$end_time1} sec \n--------------\n";

// Идем другим путем...
// Разобьем массив на части и и запустим поиск в разных процессах

$start_time = start_time();

// Допустим мы не уверены в том, что в массиве именно 10 миллионов значений, и будем бить массив на 2 части
// дабы распараллелить процесс поиска

$chunkSize = count($array) / 2;
$arrChunked = array_chunk($array, $chunkSize, true);

$end_time2 = end_time($start_time);
echo "Разбиение массива на 2 части заняло {$end_time2} sec \n";

// Форкаем процесс
// В родительском одна часть массива, в дочернем - вторая

$start_time = start_time();

$pid = pcntl_fork();
if ($pid == -1) {
    die('Не удалось породить дочерний процесс');
} else if ($pid) {
    $foundMessage = "Not found \n";
    foreach ($arrChunked[1] as $key => $value){
        if ($value == $findValue) {
            $foundMessage = "FOUND on parent process! key is {$key} \n";
            break;
        }
    }
    echo $foundMessage;

    //pcntl_wait($status); // Защита против дочерних "Зомби"-процессов
} else {
    $foundMessage = "Not found \n";
    foreach ($arrChunked[0] as $key => $value){
        if ($value == $findValue) {
            $foundMessage = "FOUND on child proces! key is {$key} \n";
            break;
        }
    }
    echo $foundMessage;
    die();
}

$end_time3 = end_time($start_time);
echo "Чистое время на параллельный поиск заняло {$end_time3} sec \n--------------\n";


// И сравнение с обычной функцией поиска значения в массиве
$start_time = start_time();

$key = array_search($findValue, $array);

$foundMessage = "Not found \n";
if ($key){
    $foundMessage = "FOUND on child proces! key is {$key} \n";
}

$end_time4 = end_time($start_time);
echo "Обычный поиск занял {$end_time4} sec \n--------------\n";

