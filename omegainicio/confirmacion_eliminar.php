<?php
session_start();
if($_SESSION['rol']!=1)
{
	header("location: ./");
}
include "../bd_conexion.php";
if(!empty($_POST))
{
	if($_POST['idusuario'] == 1){
		header("location: lista_usuarios.php");
		mysqli_close($conection);//
		exit;
	}
	$idusuario= $_POST['idusuario'];

	
$query_delete = mysqli_query($conection,"UPDATE usuario SET estado = 0 WHERE idusuario = $idusuario ");
mysqli_close($conection);//
if($query_delete){
	header("location: lista_usuarios.php");
}else{
	echo "Ha ocurrido un error";
}

}

if(empty($_REQUEST['id']) || $_REQUEST['id'] == 1 )
{
	header("Location: lista_usuarios.php");
	mysqli_close($conection);//
}else{
	include "../bd_conexion.php";
	$idusuario =$_REQUEST['id'];

	$query = mysqli_query($conection, "SELECT u.nombre, u.usuario, r.rol FROM usuario u INNER JOIN rol r ON u.rol = r.idrol WHERE u.idusuario = $idusuario");

	$result =mysqli_num_rows($query);
mysqli_close($conection);//
	if($result > 0){
		while ($data = mysqli_fetch_array($query)){

			$nombre =$data['nombre'];
			$usuario =$data['usuario'];
			$rol =$data['rol'];
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
	<title>Eliminar Usuario</title>
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
			<p>Usuario: <span><?php echo $usuario; ?></span></p>
			<p>Rol: <span><?php echo $rol; ?></span></p>
			<form method="post" action="">
				<input type="hidden" name="idusuario" value="<?php echo $idusuario;?>">
				<a href="lista_usuarios.php" class="btn_cancel"><i class="fas fa-ban"></i> Cancelar</a>
				
				<button type="submit" class="btn_ok"><i class="fas fa-trash-alt"></i> Eliminar</button>

			</form>
	</section>
	<?php include "include/footer.php" ?>
</body>
</html>