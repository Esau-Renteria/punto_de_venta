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
	if(empty($_POST['proveedor']) || empty($_POST['producto']) || empty($_POST['precio']) || $_POST['precio'] <= 0 || empty($_POST['cantidad']) || $_POST['cantidad'] <= 0) // Ponemos una condicion en la que verificamos si los campos estan vacios
	{
      $alert='<p class="msg_error"> Llena todos los campos, codigo puede ir vacio<p>'; // Si los campos estan vacios pues pedimos que los llenen
	}  
	else{
		
		// Guardamos en variables todo lo que se ha enviado
		$proveedor = $_POST['proveedor']; 
		$producto = $_POST['producto']; 
		$precio= $_POST['precio'];
		$cantidad = $_POST['cantidad'];
		$usuario_id = $_SESSION['idUser'];

		$foto = $_FILES['foto'];
		$nombre_foto = $foto['name'];
		$type = $foto['type'];
		$url_temp = $foto['tmp_name'];

		$imgProducto = 'img_producto.jpg';

		if($nombre_foto != '')
		{
			$destino = 'img/uploads/';
			$img_nombre = 'img_'.md5(date('d-m-Y H:m:s'));
			$imgProducto = $img_nombre.'.jpg';
			$src = $destino.$imgProducto;

		}


			$query_insert = mysqli_query($conection, "INSERT INTO producto(proveedor,descripcion,precio,existencia,usuario_id,foto)
			values('$proveedor','$producto','$precio','$cantidad','$usuario_id','$imgProducto')");

			if($query_insert){
					if($nombre_foto != ''){
						move_uploaded_file($url_temp,$src);
					}
			$alert='<p class="msg_save"> Registrado exitosamente<p>'; //Si todo sale bien se guardan en la BD y se muestra un msj
		}else{
			$alert='<p class="msg_error"> Ha ocurrido un error<p>'; // Si todo sale mal no se guarda nada en la BD y se muestra un msj
		 

			}
		
		
		}
		
	}
 
 ?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "include/scripts.php" ?>
	<title>Registro de Producto</title>
</head>
<body>
	<?php include "include/header.php" ?>
	<section id="container">
		<div class="form_register">
		<h1><i class="fas fa-cubes"></i>Registro Producto</h1> 
		<hr>
		<div class="alert"><?php echo isset($alert) ? $alert: ''; ?> </div>

		<form action="" method="post" enctype="multipart/form-data">
				
			<label for="proveedor">Nombre del Proveedor</label> 

			<?php 	
				$query_proveedor = mysqli_query($conection,"SELECT codproveedor, proveedor FROM proveedor WHERE estado= 1 ORDER BY proveedor ASC");
				$result_proveedor = mysqli_num_rows($query_proveedor);
				mysqli_close($conection);
			 ?>
				<select name="proveedor" id="proveedor">
					<?php 	
						if($result_proveedor > 0){
							while ($proveedor = mysqli_fetch_array($query_proveedor)) {
								# code...
								?>
									<option value="<?php echo $proveedor['codproveedor'] ?>"><?php echo $proveedor['proveedor'] ?></option>
								<?php
							}
						}
					 ?>
					
				</select>

			

			<label for="producto">Producto</label> <!-- Enlazamos la etiqueta nombre a la etiqueta al cuadro de texto-->
			<input type="text" name="producto" id="producto" placeholder="Nombre del producto">

			<label for="precio">Precio</label> <!-- Enlazamos la etiqueta email a la etiqueta al cuadro de texto-->
			<input type="number" name="precio" id="precio" placeholder="Precio">

			<label for="cantidad">Cantidad</label> <!-- Enlazamos la etiqueta password a la etiqueta al cuadro de texto-->
			<input type="number" name="cantidad" id="cantidad" placeholder="Cantidad">

				<div class="photo">
	<label for="foto">Foto</label>
        <div class="prevPhoto">
        <span class="delPhoto notBlock">X</span>
        <label for="foto"></label>
        </div>
        <div class="upimg">
        <input type="file" name="foto" id="foto">
        </div>
        <div id="form_alert"></div>
</div>
			
			<button type="submit" class="btn_save"><i class="far fa-save"></i> Registrar</button>
		</form>
		</div>
	</section>
	<?php include "include/footer.php" ?>
</body>
</html>