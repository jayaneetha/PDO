<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        echo 'START <br>';
        include 'example.php';
        $example = new example();

        echo "Insert. id = " . $example->insert() . "<br>";
        echo "Update All <br>";
        $example->updateALL();

        echo "Update (id=1)<br>";
        $example->updateOne();

        echo "Print Results<br>";
        $result = $example->results();
        foreach ($result as $value) {
            echo $value->id . " => ";
            echo $value->name . "<br>";
        }

        echo "Single Value (name,id=1) :";
        echo $example->singleValue() . "<br>";

        echo "Delete (id=1)<br>";
        $example->delete();


        echo "Print Results<br>";
        $result = $example->results();
        foreach ($result as $value) {
            echo $value->id . " => ";
            echo $value->name . "<br>";
        }

        echo "Fetch Array<br>";
        print_r($example->fetchArray());
        echo "<br>END";
        ?>
    </body>
</html>
