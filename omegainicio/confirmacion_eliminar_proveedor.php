<?php
session_start();
if($_SESSION['rol']!=1 and $_SESSION['rol'] != 2)
{
	header("location: ./");
}
include "../bd_conexion.php";
if(!empty($_POST))
{
	if(empty($_POST['idproveedor']))
	{
		header("location: lista_proveedor.php");
		mysqli_close($conection);
	}

	$idproveedor = $_POST['idproveedor'];

	
$query_delete = mysqli_query($conection,"UPDATE proveedor SET estado = 0 WHERE codproveedor = $idproveedor ");
mysqli_close($conection);//
if($query_delete){
	header("location: lista_proveedor.php");
}else{
	echo "Ha ocurrido un error";
}

}

if(empty($_REQUEST['id'])  )
{
	header("Location: lista_proveedor.php");
	mysqli_close($conection);//
}else{
	//include "../bd_conexion.php";
	$idproveedor =$_REQUEST['id'];

	$query = mysqli_query($conection, "SELECT * FROM proveedor WHERE codproveedor = $idproveedor ");

	$result =mysqli_num_rows($query);
mysqli_close($conection);//
	if($result > 0){
		while ($data = mysqli_fetch_array($query)){

			
			$proveedor =$data['proveedor'];
			$contacto =$data['contacto'];
		
		
			
		}
		}else{
			header("Location: lista_proveedor.php");
		}
	}


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "include/scripts.php" ?>
	<title>Eliminar Proveedor</title>
</head>
<body>
	<?php include "include/header.php" ?>
	<section id="container">
		<div class="data_delete">
			<il class="far fa-building fa-7x" style="color: red"></il>
			<h2>Esta seguro de eliminar?</h2>
			<p>Proveedor: <span><?php echo $proveedor; ?></span></p>
			<p>Contacto: <span><?php echo $contacto; ?></span></p>
			
			<form method="post" action="">
				<input type="hidden" name="idproveedor" value="<?php echo $idproveedor; ?>">
				<a href="lista_proveedor.php" class="btn_cancel"><i class="fas fa-ban"></i>Cancelar</a>
				<button type="submit" class="btn_ok"><i class="fas fa-trash-alt"></i> Eliminar</button>
			</form>
	</section>
	<?php include "include/footer.php" ?>
</body>
</html>