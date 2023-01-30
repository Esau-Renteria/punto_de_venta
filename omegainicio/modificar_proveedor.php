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
	if(empty($_POST['proveedor']) || empty($_POST['contacto']) || empty($_POST['telefono']) ||  empty($_POST['direccion'])) // Ponemos una condicion en la que verificamos si los campos estan vacios
	{
      $alert='<p class="msg_error"> Llena todos los campos<p>'; // Si los campos estan vacios pues pedimos que los llenen
	}  else{
		
		// Guardamos en variables todo lo que se ha enviado
		$idproveedor = $_POST['id'];
		$proveedor = $_POST['proveedor']; 
		$contacto = $_POST['contacto'];
		$telefono = $_POST['telefono'];
		$direccion = $_POST['direccion'];
	
				$sql_update = mysqli_query($conection,"UPDATE proveedor SET proveedor = '$proveedor', contacto = '$contacto', telefono = '$telefono', direccion= '$direccion' WHERE codproveedor =$idproveedor");

			if($sql_update){$alert='<p class="msg_save"> Actualizado exitosamente<p>'; //Si todo sale bien se guardan en la BD y se muestra un msj
		}else{
			$alert='<p class="msg_error"> Ha ocurrido un error<p>'; // Si todo sale mal no se guarda nada en la BD y se muestra un msj
		

			}
		}
		

	}
	//Muestra de datos
	if(empty($_REQUEST['id'])){
		header('Location: lista_clientes.php');
		mysqli_close($conection);//
	}
	$idproveedor = $_REQUEST['id'];
	$sql= mysqli_query($conection,"SELECT * FROM proveedor
		
		WHERE codproveedor = $idproveedor and estado = 1 ");
	mysqli_close($conection);//
	$result_sql = mysqli_num_rows($sql);
	if($result_sql == 0) {
			header('Location: lista_proveedor.php');
		}else{
			
			while ($data = mysqli_fetch_array($sql)){
				$idcliente =$data['codproveedor'];
				$proveedor= $data['proveedor'];
				$contacto =$data['contacto'];
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
	<title>Actualizar Proveedor</title>
</head>
<body>
	<?php include "include/header.php" ?>
	<section id="container">
		<div class="form_register">
		<h1><i class="far fa-building"></i> Actualizar Proveedor</h1> 
		<hr>
		<div class="alert"><?php echo isset($alert) ? $alert: ''; ?> </div>
<form action="" method="post">
			<input type="hidden" name="id" value="<?php echo $idproveedor ?>">
			<label for="proveedor">Nombre del Proveedor</label> 
			<input type="text" name="proveedor" id="proveedor" placeholder="Nombre del Proveedor" value="<?php echo $proveedor ?> ">

			<label for="contacto">Contacto</label> <!-- Enlazamos la etiqueta nombre a la etiqueta al cuadro de texto-->
			<input type="text" name="contacto" id="contacto" placeholder="Nombre del Contacto" value="<?php echo $contacto ?>">

		

			<label for="telefono">Telefono</label> <!-- Enlazamos la etiqueta email a la etiqueta al cuadro de texto-->
			<input type="number" name="telefono" id="telefono" placeholder="Telefono" value="<?php echo $telefono ?>">

			<label for="direccion">Direccion</label> <!-- Enlazamos la etiqueta password a la etiqueta al cuadro de texto-->
			<input type="text" name="direccion" id="direccion" placeholder="direccion" value="<?php echo $direccion ?>">

			
			<button type="submit" class="btn_save"><i class="far fa-edit"></i> Actualizar</button>
		</form>
			
		</div>
	</section>
	<?php include "include/footer.php" ?>
</body>
</html>