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
	if(empty($_POST['proveedor']) || empty($_POST['producto']) || empty($_POST['precio']) || $_POST['precio'] <= 0 || empty($_POST['id']) || empty($_POST['foto_actual']) || empty($_POST['foto_remove'])) // Ponemos una condicion en la que verificamos si los campos estan vacios
	{
      $alert='<p class="msg_error"> Llena todos los campos, codigo puede ir vacio<p>'; // Si los campos estan vacios pues pedimos que los llenen
	}  
	else{
		
		// Guardamos en variables todo lo que se ha enviado
		$codproducto = $_POST['id'];
		$proveedor = $_POST['proveedor']; 
		$producto = $_POST['producto']; 
		$precio= $_POST['precio'];
		$imgProducto = $_POST['foto_actual'];
		$imgRemove = $_POST['foto_remove'];

		$foto = $_FILES['foto'];
		$nombre_foto = $foto['name'];
		$type = $foto['type'];
		$url_temp = $foto['tmp_name'];

		$upd = '';

		if($nombre_foto != '')
		{
			$destino = 'img/uploads/';
			$img_nombre = 'img_'.md5(date('d-m-Y H:m:s'));
			$imgProducto = $img_nombre.'.jpg';
			$src = $destino.$imgProducto;

		}else{
			if($_POST['foto_actual'] != $_POST['foto_remove']){
				$imgProducto ='img_producto.jpg';
			}
		}


			$query_update = mysqli_query($conection, "UPDATE producto SET proveedor = $proveedor,descripcion = '$producto',precio = $precio,foto = '$imgProducto'
				WHERE codproducto = $codproducto");

			if($query_update){
				if(($nombre_foto != '' && ($_POST['foto_actual'] != 'img_producto.jpg')) || ($_POST['foto_actual'] != $_POST['foto_remove'])){
					unlink('img/uploads/'.$_POST['foto_actual']);
						}

					if($nombre_foto != ''){
						move_uploaded_file($url_temp,$src);
					}
			$alert='<p class="msg_save"> Actualizado exitosamente<p>'; //Si todo sale bien se guardan en la BD y se muestra un msj
		}else{
			$alert='<p class="msg_error"> Ha ocurrido un error<p>'; // Si todo sale mal no se guarda nada en la BD y se muestra un msj
		 

			}
		
		
		}
		
	}
//Validar Producto
	if(empty($_REQUEST['id'])){
		header("location: lista_producto.php");
	}else{

		$id_producto =$_REQUEST['id'];
		if(!is_numeric($id_producto)){
			header("location: lista_producto.php");
		}

		$query_producto = mysqli_query($conection,"SELECT p.codproducto,p.descripcion,p.precio,p.foto,pr.codproveedor, pr.proveedor FROM producto p INNER JOIN proveedor pr ON p.proveedor = pr.codproveedor WHERE codproducto = $id_producto AND p.estado = 1");
		$result_producto = mysqli_num_rows($query_producto);
		$foto = '';
		$classRemove = 'notBlock';

		if($result_producto > 0){
			$data_producto = mysqli_fetch_assoc($query_producto);
			if($data_producto['foto'] !=  'img_producto.jpg'){
				$classRemove ='';
				$foto= '<img id="img" src="img/uploads/'.$data_producto['foto'].'" alt="Producto">';
			}
			
		}else{
			header("location: lista_producto.php");
		}

	}
	
 ?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "include/scripts.php" ?>
	<title>Actualizar Producto</title>
</head>
<body>
	<?php include "include/header.php" ?>
	<section id="container">
		<div class="form_register">
		<h1><i class="fas fa-cubes"></i>Actualizar Producto</h1> 
		<hr>
		<div class="alert"><?php echo isset($alert) ? $alert: ''; ?> </div>

		<form action="" method="post" enctype="multipart/form-data">
			<input type="hidden" name="id" value="<?php echo $data_producto['codproducto']; ?>">
			<input type="hidden" id="foto_actual" name="foto_actual" value="<?php echo $data_producto['foto']; ?>">
			<input type="hidden" id="foto_remove" name="foto_remove" value="<?php echo $data_producto['foto']; ?>">

			<label for="proveedor">Nombre del Proveedor</label> 

			<?php 	
			//$query_proveedor = mysqli_query($conection,"SELECT * FROM proveedor WHERE estado= 1 ORDER BY proveedor ASC"); 45
				$query_proveedor = mysqli_query($conection,"SELECT codproveedor, proveedor FROM proveedor WHERE estado= 1 ORDER BY proveedor ASC");
				$result_proveedor = mysqli_num_rows($query_proveedor);
				mysqli_close($conection);
			 ?>
				<select name="proveedor" id="proveedor" class="notItemOne">
					<option value="<?php echo $data_producto['codproveedor']; ?>" selected><?php echo $data_producto['proveedor']; ?></option>
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
			<input type="text" name="producto" id="producto" placeholder="Nombre del producto" value="<?php echo $data_producto['descripcion']; ?>">

			<label for="precio">Precio</label> <!-- Enlazamos la etiqueta email a la etiqueta al cuadro de texto-->
			<input type="text" name="precio" id="precio" placeholder="Precio" value="<?php echo $data_producto['precio']; ?>">

			
				<div class="photo">
	<label for="foto">Foto</label>

        <div class="prevPhoto">
        <span class="delPhoto <?php echo $classRemove; ?>">X</span>
        <label for="foto"></label>
        	<?php echo $foto; ?>
        </div>
        <div class="upimg">
        <input type="file" name="foto" id="foto">
        </div>
        <div id="form_alert"></div>
</div>
			
			<button type="submit" class="btn_save"><i class="far fa-save"></i> Acualizar</button>
		</form>
		</div>
	</section>
	<?php include "include/footer.php" ?>
</body>
</html>