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
	if(empty($_POST['nombre']) || empty($_POST['correo']) ||  empty($_POST['usuario']) ||   empty($_POST['rol'])) // Ponemos una condicion en la que verificamos si los campos estan vacios
	{
      $alert='<p class="msg_error"> Llena todos los campos<p>'; // Si los campos estan vacios pues pedimos que los llenen
	}  
	else{
		
		// Guardamos en variables todo lo que se ha enviado
		$idUsuario = $_POST['id'];
		$nombre = $_POST['nombre']; 
		$email = $_POST['correo'];
		$user = $_POST['usuario'];
		$clave = md5($_POST['clave']);
		$rol = $_POST['rol'];

		// Mediante una sentencia checamos si ya existe un usuario o un correo igual al que nos acaban de mandar
		$query = mysqli_query($conection, "SELECT * FROM usuario WHERE (usuario = '$user' AND idusuario != $idUsuario) OR (correo = '$email' AND  idusuario != $idUsuario)");

		// Guardamos lo que nos mandaron en un arreglo
		$result = mysqli_fetch_array($query);
		

		// si el arreglo tiene algo significa que si hay registros similares
		if($result > 0){ 
			$alert='<p class="msg_error"> El correo o usuario ya existen<p>'; // Le decimos que no puede haber 2 correos/usuarios similares
		}else{
			// Si llegamos aca significa que no hay registros de usuarios/correos iguales entonces ahora si los guardamos
			if (empty($_POST['clave'])){

				$sql_update = mysqli_query($conection,"UPDATE usuario SET nombre = '$nombre', correo = '$email',usuario = '$user',rol = '$rol' WHERE idusuario =$idUsuario");
			}else{
				$sql_update = mysqli_query($conection,"UPDATE usuario SET nombre = '$nombre', correo = '$email', clave='$clave',usuario = '$user',rol = '$rol' WHERE idusuario =$idUsuario");
			}



			

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
	$iduser = $_REQUEST['id'];
	$sql= mysqli_query($conection,"SELECT u.idusuario, u.nombre, u.correo, u.usuario, (u.rol) as idrol, (r.rol) as rol FROM usuario u 
		INNER JOIN rol r 
		on u.rol = r.idrol
		WHERE idusuario = $iduser and estado = 1 ");

	mysqli_close($conection);//
	$result_sql = mysqli_num_rows($sql);
	if($result_sql == 0) {
			header('Location: lista_usuarios.php');
		}else{
			$option='';
			while ($data = mysqli_fetch_array($sql)){
				$iduser =$data['idusuario'];
				$nombre=$data['nombre'];
				$correo =$data['correo'];
				$usuario =$data['usuario'];
				$idrol =$data['idrol'];
				$rol =$data['rol'];
				if($idrol == 1){
					$option = '<option value="'.$idrol.'" select>'.$rol.'</option>';
				}
				else if($idrol == 2){
						$option = '<option value="'.$idrol.'" select>'.$rol.'</option>';
				}
				else if($idrol == 3){
						$option = '<option value="'.$idrol.'" select>'.$rol.'</option>';
				}

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
		<h1><i class="fas fa-edit"></i> Actualizar Usuario</h1> 
		<hr>
		<div class="alert"><?php echo isset($alert) ? $alert: ''; ?> </div>

		<form action="" method="post">
			<input type="hidden" name="id" value="<?php echo $iduser; ?>">
			<label for="nombre">Nombre</label> <!-- Enlazamos la etiqueta nombre a la etiqueta al cuadro de texto-->
			<input type="text" name="nombre" id="nombre" placeholder="Nombre Completo" value="<?php echo $nombre ?>">

			<label for="correo">E-mail</label> <!-- Enlazamos la etiqueta email a la etiqueta al cuadro de texto-->
			<input type="email" name="correo" id="email" placeholder="example@gmail.com" value="<?php echo $correo ?>">

			<label for="usuario">Usuario</label> <!-- Enlazamos la etiqueta email a la etiqueta al cuadro de texto-->
			<input type="text" name="usuario" id="usuario" placeholder="Agrega tu apodo o tu nombre" value="<?php echo $usuario ?>">

			<label for="clave">Password</label> <!-- Enlazamos la etiqueta password a la etiqueta al cuadro de texto-->
			<input type="password" name="clave" id="clave" placeholder="Mas de 8 caracteres & num">

			<label for="rol">Rol</label> <!-- ^!Investigar como enlazarlo a la BD-->

			<?php 
			include "../bd_conexion.php"; 
			$query_rol = mysqli_query($conection, "SELECT * FROM rol");
			mysqli_close($conection);//
			$result_rol = mysqli_num_rows($query_rol);
			
			
			?>

			<select name="rol" id="rol" class="notItemOne">
				<?php 
				echo $option;
					if($result_rol > 0){

						while ($rol = mysqli_fetch_array($query_rol)) {
							?>
							<option value="<?php echo $rol["idrol"]; ?>"><?php echo $rol["rol"] ?></option>
					<?php
				}
			} 
			?>
			</select>
			
			<button type="submit" class="btn_save"><i class="fas fa-edit"></i>Actualizar</button>
		</form>
		</div>
	</section>
	<?php include "include/footer.php" ?>
</body>
</html>