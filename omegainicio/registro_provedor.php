<?php 
session_start();

if($_SESSION['rol']!=1 and $_SESSION['rol']!=2)
{
	header("location: ./");
}

include "../bd_conexion.php"; // Primero conectamos la BD
if(!empty($_POST)) //Checamos si la variable post tiene algo, osea si presionaron el boton pues
{
	$alert=''; // Declaramos la variable alert
	if(empty($_POST['proveedor']) || empty($_POST['contacto']) || empty($_POST['telefono']) || empty($_POST['direccion'])) // Ponemos una condicion en la que verificamos si los campos estan vacios
	{
      $alert='<p class="msg_error"> Llena todos los campos, codigo puede ir vacio<p>'; // Si los campos estan vacios pues pedimos que los llenen
	}  
	else{
		
		// Guardamos en variables todo lo que se ha enviado
		$proveedor = $_POST['proveedor']; 
		$contacto = $_POST['contacto']; 
		$telefono = $_POST['telefono'];
		$direccion = $_POST['direccion'];
		$usuario_id = $_SESSION['idUser'];


			$query_insert = mysqli_query($conection, "INSERT INTO proveedor(proveedor,contacto,telefono,direccion,usuario_id)
			values('$proveedor','$contacto','$telefono','$direccion','$usuario_id')");

			if($query_insert){$alert='<p class="msg_save"> Registrado exitosamente<p>'; //Si todo sale bien se guardan en la BD y se muestra un msj
		}else{
			$alert='<p class="msg_error"> Ha ocurrido un error<p>'; // Si todo sale mal no se guarda nada en la BD y se muestra un msj
		 

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
	<title>Registro de Proveedor</title>
</head>
<body>
	<?php include "include/header.php" ?>
	<section id="container">
		<div class="form_register">
		<h1>Registro Proveedor</h1> 
		<hr>
		<div class="alert"><?php echo isset($alert) ? $alert: ''; ?> </div>

		<form action="" method="post">
				
			<label for="proveedor">Nombre del Proveedor</label> 
			<input type="text" name="proveedor" id="proveedor" placeholder="Nombre del Proveedor">

			<label for="contacto">Contacto</label> <!-- Enlazamos la etiqueta nombre a la etiqueta al cuadro de texto-->
			<input type="text" name="contacto" id="contacto" placeholder="Nombre del Contacto">

		

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