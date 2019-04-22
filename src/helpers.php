<?php
function success(array $data){
    $rdata["status"] = "success";
    $rdata["data"] =$data;
    return $rdata;
}

function error($message){
    $data["status"] = "error";
    $data["message"] = $message;
    return $data;
}