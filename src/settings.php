<?php
$db = new PDO("mysql:host=db;port=3306;dbname=praywall;charset=utf8", 'root', 'root');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
