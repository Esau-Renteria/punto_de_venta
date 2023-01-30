<?php 
session_start();
if($_SESSION['rol']!=1)
{
	header("location: ./");
}
include "../bd_conexion.php"; // Primero conectamos la BD
if(!empty($_POST)) //Checamos si la variable post tiene algo, osea si presionaron el boton pues
{
	$alert=''; // Declaramos la variable alert
	if(empty($_POST['nombre']) || empty($_POST['correo']) ||  empty($_POST['usuario']) ||  empty($_POST['clave']) || empty($_POST['rol'])) // Ponemos una condicion en la que verificamos si los campos estan vacios
	{
      $alert='<p class="msg_error"> Llena todos los campos<p>'; // Si los campos estan vacios pues pedimos que los llenen
	}  
	else{
		
		// Guardamos en variables todo lo que se ha enviado
		$nombre = $_POST['nombre']; 
		$email = $_POST['correo'];
		$user = $_POST['usuario'];
		$clave = md5($_POST['clave']);
		$rol = $_POST['rol'];

		// Mediante una sentencia checamos si ya existe un usuario o un correo igual al que nos acaban de mandar
		$query = mysqli_query($conection, "SELECT * FROM usuario WHERE usuario = '$user' OR correo = '$email' ");
	
		// Guardamos lo que nos mandaron en un arreglo
		$result = mysqli_fetch_array($query);

		// si el arreglo tiene algo significa que si hay registros similares
		if($result > 0){ 
			$alert='<p class="msg_error"> El correo o usuario ya existen<p>'; // Le decimos que no puede haber 2 correos/usuarios similares
		}else{
			// Si llegamos aca significa que no hay registros de usuarios/correos iguales entonces ahora si los guardamos
			$query_insert = mysqli_query($conection, "INSERT INTO usuario(nombre,correo,usuario,clave,rol)
			values('$nombre','$email','$user','$clave','$rol')");

			if($query_insert){$alert='<p class="msg_save"> Registrado exitosamente<p>'; //Si todo sale bien se guardan en la BD y se muestra un msj
		}else{
			$alert='<p class="msg_error"> Ha ocurrido un error<p>'; // Si todo sale mal no se guarda nada en la BD y se muestra un msj
		} 

			}
		}
	}
 
 ?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "include/scripts.php" ?>
	<title>Registro de Usuario</title>
</head>
<body>
	<?php include "include/header.php" ?>
	<section id="container">
		<div class="form_register">
		<h1><i class="fas fa-user-plus"></i> Registro usuario</h1> 
		<hr>
		<div class="alert"><?php echo isset($alert) ? $alert: ''; ?> </div>

		<form action="" method="post">
			<label for="nombre">Nombre</label> <!-- Enlazamos la etiqueta nombre a la etiqueta al cuadro de texto-->
			<input type="text" name="nombre" id="nombre" placeholder="Nombre Completo">

			<label for="correo">E-mail</label> <!-- Enlazamos la etiqueta email a la etiqueta al cuadro de texto-->
			<input type="email" name="correo" id="email" placeholder="example@gmail.com">

			<label for="usuario">Usuario</label> <!-- Enlazamos la etiqueta email a la etiqueta al cuadro de texto-->
			<input type="text" name="usuario" id="usuario" placeholder="Agrega tu apodo o tu nombre">

			<label for="clave">Password</label> <!-- Enlazamos la etiqueta password a la etiqueta al cuadro de texto-->
			<input type="password" name="clave" id="clave" placeholder="Mas de 8 caracteres & num">

			<label for="rol" id="rol">Rol</label> <!-- ^!Investigar como enlazarlo a la BD-->

			<?php $query_rol = mysqli_query($conection, "SELECT * FROM rol");
			mysqli_close($conection);//
			$result_rol = mysqli_num_rows($query_rol);
			
			
			?>

			<select name="rol" id="rol">
				<?php 
					if($result_rol > 0){
						while ($rol = mysqli_fetch_array($query_rol)) {
							?>
							<option value="<?php echo $rol["idrol"]; ?>"><?php echo $rol["rol"] ?></option>
					<?php
				}
			} 
			?>
			</select>
			
			<button type="submit" class="btn_save"><i class="far fa-save"></i> Registrar</button>
		</form>
		</div>
	</section>
	<?php include "include/footer.php" ?>
</body>
</html>