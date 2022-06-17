<?php 
function formatDate(int $date) {
    return date("l, F j, o \a\\t g:i A", $date);
}