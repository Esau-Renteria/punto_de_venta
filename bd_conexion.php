<?php 
	// Aqui realiazamos la conexion para evitar tener que mandarla a llamar en cada hoja que creemos

	$host = 'localhost'; // nombre del servidor
	$user = 'root'; // usuario
	$password = ''; // contraseña
	$bd = 'somega'; // nombre de la BD

	$conection = @mysqli_connect($host,$user,$password,$bd); // guardamos la conexion en una varibale para que sea mas facil manipularla, le pasamos como parametros las variables antes creadas

	

	// probamos la conexion
	if(!$conection){
		echo "Algo salio mal :(";
	}
	
 ?>

