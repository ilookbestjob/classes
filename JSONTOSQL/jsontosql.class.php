<?php
class jsontosql

{

    function load($data, $connection, $table, $fields, $conversion = [], $unique = [])
    {


        foreach ($data as $value) {

            $value = json_decode(json_encode($value), true);






            $insertSQL = "insert into $table set ";
            $updateSQL = "update $table set ";
            $where = "";
            $first = true;

            foreach ($fields as $field => $source) {

                $currentvalue = (isset($value[$source]) ? $value[$source]  : "");
                // $convertedvalue = (isset($conversion[$field]) ? (isset($conversion[$field][$currentvalue]) ? $conversion[$field][$currentvalue] : $currentvalue) . "'" : $currentvalue);
                $convertedvalue = $currentvalue;
                if (isset($conversion[$field])) $convertedvalue = call_user_func_array($conversion[$field], [ $currentvalue ]);
                $where .= in_array($field, $unique) ? ($first ? " where " : " and ") . $field . "='" . $convertedvalue."'" : "";
                $insertSQL .= ($first ? "" : ",") . $field . "='" . $convertedvalue."'";
                $updateSQL .= ($first ? "" : ",") . $field . "='" . $convertedvalue."'";

                $first = false;
            }

            $selectSQL = "select count(*) as quantity from $table $where";
            $updateSQL .= $where;
            $sql_result = mysqli_query($connection, $selectSQL);

            $sql_row = mysqli_fetch_array($sql_result);
            echo $selectSQL."<br>";
            if ($sql_row['quantity'] > 0) {
                $sql_result = mysqli_query($connection, $updateSQL);
                echo $updateSQL."<br>";
                
            } else {
                $sql_result = mysqli_query($connection, $insertSQL);

                echo $insertSQL."<br>";
            }
        }
    }
    function __call($func, $args)
    {
        echo $func;
    }
}
