<?php
session_start();
if($_SESSION['rol']!=1)
{
	header("location: ./");
}

	include "../bd_conexion.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "include/scripts.php" ?>
	<title>Lista de Usuarios</title>
</head>
<body>
	<?php include "include/header.php" ?>
	<section id="container">
		<?php
		$busqueda =strtolower($_REQUEST['busqueda']);
		if(empty($busqueda)){
			header("Location: lista_usuarios.php");
			mysqli_close($conection);//
		}
		?>

		<h1><i class="fas fa-users"></i>Lista de Usuarios</h1>
		<a href="registro_usuario.php" class="btn_new"><i class="fas fa-user-plus"></i> Crear Usuarios </a>

		<form action="buscar_usuario.php" method="get" class="form_search">
			<input type="text" name="busqueda" id="busqueda" placeholder="Buscar" value="<?php echo $busqueda; ?>">
			<button type="submit" value="Buscar" class="btn_search"><i class="fas fa-search"></i></button>
		</form>
		<table>
			<tr>
				<th>ID</th>
				<th>Nombre</th>
				<th>Correo</th>
				<th>Usuario</th>
				<th>Rol</th>
				<th>Acciones</th>
			</tr>

				<?PHP 
				//paginador
				$rol = '';
			if($busqueda == 'administrador')
			{
				$rol = " OR rol LIKE '%1%' ";

			}else if($busqueda == 'supervisor'){

				$rol = " OR rol LIKE '%2%' ";

			}else if($busqueda == 'vendedor'){

				$rol = " OR rol LIKE '%3%' ";
			}

				$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_register FROM usuario 
																WHERE ( idusuario LIKE '%$busqueda%' OR 
																		nombre LIKE '%$busqueda%' OR 
																		correo LIKE '%$busqueda%' OR 
																		usuario LIKE '%$busqueda%' 
																		$rol  ) 
																AND estado = 1  ");

				$result_register = mysqli_fetch_array($sql_registe);
				$total_register = $result_register['total_register'];
				$por_pagina = 6;

				if(empty($_GET['pagina']))
				{
					$pagina =1;
				}else{
					$pagina = $_GET['pagina'];
				}
				$desde = ($pagina-1) * $por_pagina;
				$total_paginas = ceil($total_register / $por_pagina);

				
			$query = mysqli_query($conection,"SELECT u.idusuario, u.nombre, u.correo, u.usuario, r.rol FROM usuario u INNER JOIN rol r ON u.rol = r.idrol 
										WHERE 
										( u.idusuario LIKE '%$busqueda%' OR 
											u.nombre LIKE '%$busqueda%' OR 
											u.correo LIKE '%$busqueda%' OR 
											u.usuario LIKE '%$busqueda%' OR 
											r.rol    LIKE  '%$busqueda%' ) 
										AND
										estado = 1 ORDER BY u.idusuario ASC LIMIT $desde,$por_pagina 
				");
				mysqli_close($conection);//
				$result = mysqli_num_rows($query);
				if($result > 0){

				while($data = mysqli_fetch_array($query)){
				?>
				<tr>
				<td><?php echo $data["idusuario"] ?> </td>
				<td><?php echo $data["nombre"] ?> </td>
				<td><?php echo $data["correo"] ?> </td>
				<td><?php echo $data["usuario"] ?> </td>
				<td><?php echo $data["rol"] ?> </td>
				<td>
					<a class="link_edit" href="modificar_usuario.php?id=<?php echo $data["idusuario"]; ?>"><i class="fas fa-edit"></i> Editar</a>

					<?php if($data["idusuario"] != 1){
					?>
					|
					<a class="link_delete" href="confirmacion_eliminar.php?id=<?php echo $data["idusuario"]; ?>"><i class="fas fa-edit"></i> Eliminar</a>
					<?php } ?>
				</td>
				</tr>
			<?php
			}
			}

			?>
		</table>
		<?php
		if($total_register != 0){

		?>
		<div class="paginador">
			<ul>
			<?php 
				if($pagina != 1)
				{
			 ?>
				<li><a href="?pagina=<?php echo 1; ?>&busqueda=<?php echo $busqueda; ?>"><i class="fas fa-step-backward"></i></a></li>
				<li><a href="?pagina=<?php echo $pagina-1; ?>&busqueda=<?php echo $busqueda; ?>"><i class="fas fa-caret-left"></i></a></li>
			<?php 
				}
				for ($i=1; $i <= $total_paginas; $i++) { 
					# code...
					if($i == $pagina)
					{
						echo '<li class="pageSelected">'.$i.'</li>';
					}else{
						echo '<li><a href="?pagina='.$i.'&busqueda='.$busqueda.'">'.$i.'</a></li>';
					}
				}

				if($pagina != $total_paginas)
				{
			 ?>
				<li><a href="?pagina=<?php echo $pagina + 1; ?>&busqueda=<?php echo $busqueda; ?>"><i class="fas fa-caret-right"></i></a></li>
				<li><a href="?pagina=<?php echo $total_paginas; ?>&busqueda=<?php echo $busqueda; ?> "><i class="fas fa-step-forward"></i></a></li>
			<?php } ?>
			</ul>
</div>
<?php } ?>

	</section>
	<?php include "include/footer.php" ?>
</body>
</html>