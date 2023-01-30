<?php
session_start();
if($_SESSION['rol']!=1 and $_SESSION['rol'] != 2)
{
	header("location: ./");
}
include "../bd_conexion.php";
if(!empty($_POST))
{
	if(empty($_POST['idcliente']))
	{
		header("location: lista_clientes.php");
		mysqli_close($conection);
	}

	$idcliente= $_POST['idcliente'];

	
$query_delete = mysqli_query($conection,"UPDATE cliente SET estado = 0 WHERE idcliente = $idcliente ");
mysqli_close($conection);//
if($query_delete){
	header("location: lista_clientes.php");
}else{
	echo "Ha ocurrido un error";
}

}

if(empty($_REQUEST['id'])  )
{
	header("Location: lista_clientes.php");
	mysqli_close($conection);//
}else{
	//include "../bd_conexion.php";
	$idcliente =$_REQUEST['id'];

	$query = mysqli_query($conection, "SELECT * FROM cliente WHERE idcliente = $idcliente ");

	$result =mysqli_num_rows($query);
mysqli_close($conection);//
	if($result > 0){
		while ($data = mysqli_fetch_array($query)){

			$nit =$data['nit'];
			$nombre =$data['nombre'];
		
			
		}
		}else{
			header("Location: lista_usuarios.php");
		}
	}


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "include/scripts.php" ?>
	<title>Eliminar Cliente</title>
</head>
<body>
	<?php include "include/header.php" ?>
	<section id="container">
		<div class="data_delete">
			<i class="fas fa-user-times fa-7x" style="color:red;"></i>
			<br>
			<br>
			<h2>Esta seguro de eliminar?</h2>
			<p>Nombre: <span><?php echo $nombre; ?></span></p>
			<p>Clave: <span><?php echo $nit; ?></span></p>
			
			<form method="post" action="">
				<input type="hidden" name="idcliente" value="<?php echo $idcliente; ?>">
				<a href="lista_clientes.php" class="btn_cancel"><i class="fas fa-ban"></i>Cancelar</a>
				<button type="submit" class="btn_ok"><i class="fas fa-trash-alt"></i> Eliminar</button>

			</form>
	</section>
	<?php include "include/footer.php" ?>
</body>
</html>