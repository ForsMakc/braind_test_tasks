<?php
const MIN = 0;
const MAX = 1000;
const ERROR = 0;
const WARNING = 1;

$n = $_POST['errors_count'];
$m = $_POST['warnings_count'];
$commitCount = 0;

if ((!empty($n)) && (!empty($m))) {
    correctCode($n,$m,$commitCount);
    $result = "<hr><p>Количество коммитов = $commitCount</p>";
}

//алгоритм работает из следующих соображений: поскольку коммиты ошибок не меняют состояние количества ворнингов, а ворнинги могут влиять на оба типа ошибок, то надо взаимодействоать с ними (ворнингами)
function correctCode(&$n,&$m,&$commitCount){
    if ($m % 2 == 0) { //случай чётного числа ворнингов
        //условие проверяет следующую ситуацию: если после редукции ворнингов останется чётное число ошибок,
        //то можно выполнить редкуцию для обоих типов ошибок, иначе привести количество ворнингов к нечётному числу
        if ((($m / 2) + $n) % 2 == 0) {
            reduce($n,$m,$commitCount);
            return;
        } else commit($n,$m,$commitCount,WARNING,1); 
    }
            
    while ($m != 1) commit($n,$m,$commitCount,WARNING,2); //сводим их количество до 1 по правилу "- 2 ворнинга + 1 ошибка"
    if ($n % 2 != 0) { //если количество ошибок нечётное, то создаём ворнинг, и за счёт 2 ворнингов собираем чётное количество ошибок, и избавляемся от них
        commit($n,$m,$commitCount,WARNING,1);
        reduce($n,$m,$commitCount);
    } else {
        for ($i = 0; $i < 3; $i++) commit($n,$m,$commitCount,WARNING,1);  //если количество ошибок чётное, то необходимо избавится от ворнингов, не влияя на чётность ошибок,
        // следовательно нужно сделать чётное количество ошибок и ворнингов, что для данной ситуации получается благодаря исправлению 1 ворнинга за 3 коммита
        reduce($n,$m,$commitCount);
    }
}

/*
* Гарантия редкуции всех ошибок возможна в следущей ситуации: 
* 1) число ворнингов чётное
* 2) после полной редкуции ворнингов число ошибок чётное
* удовлетворяя данные условия можно сделать редкуцию
*/
function reduce(&$n,&$m,&$commitCount){    
    while ($m) commit($n,$m,$commitCount,WARNING,2);
    while ($n) commit($n,$m,$commitCount,ERROR,2);
}

function commit(&$n,&$m,&$commitCount,$whatCommit,$count){
    switch ($whatCommit) {
        case ERROR: {
            //правило с исправлением 1 ошибкой не изменяет количество ни ошибок, ни ворнингов, поэтому не используем
            if ($count == 2) $n -= $count;
            break;
        }
        case WARNING: {
            if ($count == 1) $m++;
            else if ($count == 2) {
                $m -= $count;
                $n++;
            }
            break;
        }
    }
    $commitCount++;
}
?>

<html>
    <body>
        <form method="POST">
            <label for="warnings">Количество ворнингов:</label>
            <input type="number" name="warnings_count" id="warnings" required min="<?=MIN?>" max="<?=MAX?>">
            <br><br>
            <label for="errors">Количество ошибок:</label>
            <input type="number" name="errors_count" id="errors" required min="<?=MIN?>" max="<?=MAX?>">
            <br>
            <br><input type="submit" value="Узнать число коммитов">
        </form>
        <?php if (isset($result)) echo $result; ?>
    </body>
</html>


