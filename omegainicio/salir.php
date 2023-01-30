<?php 
session_start(); // iniciamos la sesion
session_destroy(); // Destruimos la sesion ya que si no se hace no permitira acceder al login por las validaciones realizadas en la pagina de index.php
header('location: ../'); // Nos manda a la pagina de Login
 ?>