<?php 
session_start();

include "../bd_conexion.php"; // Primero conectamos la BD
if(!empty($_POST)) //Checamos si la variable post tiene algo, osea si presionaron el boton pues
{
	$alert=''; // Declaramos la variable alert
	if(empty($_POST['nombre']) || empty($_POST['telefono']) ||  empty($_POST['direccion'])) // Ponemos una condicion en la que verificamos si los campos estan vacios
	{
      $alert='<p class="msg_error"> Llena todos los campos<p>'; // Si los campos estan vacios pues pedimos que los llenen
	}  
	else{
		
		// Guardamos en variables todo lo que se ha enviado
		$idcliente = $_POST['id'];
		$nit = $_POST['nit']; 
		$nombre = $_POST['nombre'];
		$telefono = $_POST['telefono'];
		$direccion = $_POST['direccion'];

		$result = 0;
		if(is_numeric($nit) and $nit !=0)
		{
			// Mediante una sentencia checamos si ya existe un usuario o un correo igual al que nos acaban de mandar
		$query = mysqli_query($conection, "SELECT * FROM cliente WHERE (nit = '$nit' and idcliente != $idcliente)" );
		$result = mysqli_fetch_array($query);
		
		}
		

		// Guardamos lo que nos mandaron en un arreglo
		
		

		// si el arreglo tiene algo significa que si hay registros similares
		if($result > 0){ 
			$alert='<p class="msg_error"> Ya existe el codigo<p>'; // Le decimos que no puede haber 2 correos/usuarios similares
		}else{
			if($nit == "")
			{
				$nit =0;
			}
			

				$sql_update = mysqli_query($conection,"UPDATE cliente SET nit = '$nit', nombre = '$nombre', telefono = '$telefono', direccion= '$direccion' WHERE idcliente =$idcliente");

			if($sql_update){$alert='<p class="msg_save"> Actualizado exitosamente<p>'; //Si todo sale bien se guardan en la BD y se muestra un msj
		}else{
			$alert='<p class="msg_error"> Ha ocurrido un error<p>'; // Si todo sale mal no se guarda nada en la BD y se muestra un msj
		} 

			}
		}
		

	}
	//Muestra de datos
	if(empty($_REQUEST['id'])){
		header('Location: lista_usuarios.php');
		mysqli_close($conection);//
	}
	$idcliente = $_REQUEST['id'];
	$sql= mysqli_query($conection,"SELECT * FROM cliente
		
		WHERE idcliente = $idcliente and estado = 1 ");
	mysqli_close($conection);//
	$result_sql = mysqli_num_rows($sql);
	if($result_sql == 0) {
			header('Location: lista_clientes.php');
		}else{
			
			while ($data = mysqli_fetch_array($sql)){
				$idcliente =$data['idcliente'];
				$nit = $data['nit'];
				$nombre =$data['nombre'];
				$telefono =$data['telefono'];
				$direccion =$data['direccion'];
				
				

			}
		}
	
 
 ?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "include/scripts.php" ?>
	<title>Actualizar Usuario</title>
</head>
<body>
	<?php include "include/header.php" ?>
	<section id="container">
		<div class="form_register">
		<h1>Actualizar Cliente</h1> 
		<hr>
		<div class="alert"><?php echo isset($alert) ? $alert: ''; ?> </div>

			<form action="" method="post">
			<input type="hidden" name="id" value="<?php echo $idcliente; ?>">
			<label for="nit">Codigo</label> 
			<input type="number" name="nit" id="nit" placeholder="codigo" value="<?php echo $nit; ?>">

			<label for="nombre">Nombre</label> <!-- Enlazamos la etiqueta nombre a la etiqueta al cuadro de texto-->
			<input type="text" name="nombre" id="nombre" placeholder="Nombre Completo" value="<?php echo $nombre; ?>">

		

			<label for="telefono">Telefono</label> <!-- Enlazamos la etiqueta email a la etiqueta al cuadro de texto-->
			<input type="number" name="telefono" id="telefono" placeholder="Telefono" value="<?php echo $telefono; ?>">

			<label for="direccion">Direccion</label> <!-- Enlazamos la etiqueta password a la etiqueta al cuadro de texto-->
			<input type="text" name="direccion" id="direccion" placeholder="direccion" value="<?php echo $direccion; ?>">

			
			<button type="submit" class="btn_save"><i class="fas fa-edit"></i>Actualizar</button>
		</form>
		</div>
	</section>
	<?php include "include/footer.php" ?>
</body>
</html>