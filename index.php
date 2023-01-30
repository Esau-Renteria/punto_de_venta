 <!-- En esta pagina es el login el cual solo permite acceder al sistema a las personas que se ecuenten debidamente registradas --> 

<?php 
$alert = '';
session_start(); // Iniciamos la sesion
if(!empty($_SESSION['active'])){ // validamos que la sesion este activada si es asi no lo dejamos entrar al menu de login
	header('location: omegainicio/');
}else 
{
if(!empty($_POST)) // Verficamos que ambos campos tengan datos dentro
{
	if(empty($_POST['usuario']) || empty($_POST['clave']))
	{
		$alert= 'Ingrese sus credenciales';
	}
	else{
		require_once "bd_conexion.php"; // Conectamos la BD
		$user = mysqli_real_escape_string($conection,$_POST['usuario']); //Invalidamos ataques de SQL Injection
		$pass = md5(mysqli_real_escape_string($conection,$_POST['clave'])); //Invalidamos ataques de SQL Injection ademas la contraseña se trae encriptada

		$query = mysqli_query($conection,"SELECT * FROM usuario where usuario= '$user' and clave = '$pass' "); // Buscamos el usuario y la constraseña dentro de la BD
		mysqli_close($conection);//
		$result = mysqli_num_rows($query);
		if($result > 0)
		{
			$data = mysqli_fetch_array($query); // Guardamos los datos del usuario en un array en caso de necesitarlos mas adelante
			
			$_SESSION['active'] = true; // activamos la sesion
			$_SESSION['idUser'] = $data['idusuario'];
			$_SESSION['nombre'] = $data['nombre'];
			$_SESSION['email'] = $data['correo'];
			$_SESSION['user'] = $data['usuario'];
			$_SESSION['rol'] = $data['rol'];

			header('location: omegainicio/'); // lo mandamos la pagina de inicio

				}else{ // En caso de que las credenciales sean incorrectas pedimos que ingrese unas nuevas
					$alert= 'Sus credenciales son incorrectas, intente nuevamente';
					session_destroy();
				}
	}
}
}
 ?>

<!DOCTYPE html>

<html>
<head>
	<meta charset="utf-8">
	<title>ΩMEGA-Login</title>
	<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
	<section id="container">
		<form action="" method="post">
			<h3>Ω</h3>
			<img src="img/login2.png" alt="Login">

			<input type="text" name="usuario" placeholder="Usuario">
			<input type="password" name="clave" placeholder="Contrasena">
			<div class="alert"><?php echo isset($alert)? $alert : '';?></div>
			<input type="submit" name="Enviar">
		</form>
	</section>
</body>
</html>