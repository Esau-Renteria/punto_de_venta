<?php 
session_start();

include "../bd_conexion.php"; // Primero conectamos la BD
if(!empty($_POST)) //Checamos si la variable post tiene algo, osea si presionaron el boton pues
{
	$alert=''; // Declaramos la variable alert
	if(empty($_POST['nombre']) || empty($_POST['telefono']) ||  empty($_POST['direccion'])) // Ponemos una condicion en la que verificamos si los campos estan vacios
	{
      $alert='<p class="msg_error"> Llena todos los campos, codigo puede ir vacio<p>'; // Si los campos estan vacios pues pedimos que los llenen
	}  
	else{
		
		// Guardamos en variables todo lo que se ha enviado
		$nit = $_POST['nit']; 
		$nombre = $_POST['nombre']; 
		$telefono = $_POST['telefono'];
		$direccion = $_POST['direccion'];
		$usuario_id = $_SESSION['idUser'];

		$result = 0;

		if(is_numeric($nit) and $nit !=0)
		{
			$query = mysqli_query($conection, "SELECT * FROM cliente WHERE nit = '$nit'");
			$result = mysqli_fetch_array($query);
		}
		
		if($result>0){
			$alert='<p class="msg_error"> El codigo ya existe<p>';

		}else{

			$query_insert = mysqli_query($conection, "INSERT INTO cliente(nit,nombre,telefono,direccion,usuario_id)
			values('$nit','$nombre','$telefono','$direccion','$usuario_id')");

			if($query_insert){$alert='<p class="msg_save"> Registrado exitosamente<p>'; //Si todo sale bien se guardan en la BD y se muestra un msj
		}else{
			$alert='<p class="msg_error"> Ha ocurrido un error<p>'; // Si todo sale mal no se guarda nada en la BD y se muestra un msj
		} 

			}
		
		
		}
		mysqli_close($conection);
	}
 
 ?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "include/scripts.php" ?>
	<title>Registro de Cliente</title>
</head>
<body>
	<?php include "include/header.php" ?>
	<section id="container">
		<div class="form_register">
		<h1>Registro Cliente</h1> 
		<hr>
		<div class="alert"><?php echo isset($alert) ? $alert: ''; ?> </div>

		<form action="" method="post">
				
			<label for="nit">Codigo</label> 
			<input type="number" name="nit" id="nit" placeholder="codigo">

			<label for="nombre">Nombre</label> <!-- Enlazamos la etiqueta nombre a la etiqueta al cuadro de texto-->
			<input type="text" name="nombre" id="nombre" placeholder="Nombre Completo">

		

			<label for="telefono">Telefono</label> <!-- Enlazamos la etiqueta email a la etiqueta al cuadro de texto-->
			<input type="number" name="telefono" id="telefono" placeholder="Telefono">

			<label for="direccion">Direccion</label> <!-- Enlazamos la etiqueta password a la etiqueta al cuadro de texto-->
			<input type="text" name="direccion" id="direccion" placeholder="direccion">

			
			<button type="submit" class="btn_save"><i class="far fa-save"></i> Registrar</button>
		</form>
		</div>
	</section>
	<?php include "include/footer.php" ?>
</body>
</html>