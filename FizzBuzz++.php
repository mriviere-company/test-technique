<?php
    if (!empty($argv[1])) {
        $int = intval($argv[1]);
        $return = null;
        if ($int) {
            if ($int % 3 === 0 && $int != 3)
                $return = 'FIZZ';
            if ($int % 5 === 0 && $int != 5)
                $return .= 'BUZZ';
            $squareInt = round(sqrt($int));
            $primary = true;
            if ($squareInt > 0) {
                while ($squareInt) {
                    if ($int % $squareInt === 0 && $squareInt > 1)
                        $primary = false;
                    $squareInt--;
                }
                if ($primary)
                    $return = 'FIZZBUZZ++';
            }
            if ($return)
                echo $return;
                file_put_contents('fizzbuzz', $return, FILE_APPEND);
        } else {
            echo 'You need to enter a digit parameter.';
        }
    } else {
        echo 'You need to enter a parameter.';
    }