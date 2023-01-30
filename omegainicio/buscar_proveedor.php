<?php
session_start();
if($_SESSION['rol']!=1 and $_SESSION['rol']!=2)
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
	<title>Lista de Proveedores</title>
</head>
<body>
	<?php include "include/header.php" ?>
	<section id="container">
		<?php
		$busqueda =strtolower($_REQUEST['busqueda']);
		if(empty($busqueda)){
			header("Location: lista_proveedor.php");
			mysqli_close($conection);//
		}
		?>
<h1><i class="fas fa-users"></i>Lista de Proveedores</h1>
		<a href="registro_provedor.php" class="btn_new"><i class="fas fa-plus"></i> Registrar Proveedor </a>

		<form action="buscar_proveedor.php" method="get" class="form_search">
			<input type="text" name="busqueda" id="busqueda" placeholder="Buscar" value="<?php echo $busqueda; ?>">
			<button type="submit" value="Buscar" class="btn_search"><i class="fas fa-search"></i></button>
		</form>
		<table>
			<tr>
				<th>ID</th>
				<th>Proveedor</th>
				<th>Contacto</th>
				<th>Telefono</th>
				<th>Direccion</th>
				<th>Fecha</th>
				<th>Acciones</th>
			</tr>

				<?PHP 
				//paginador
				
				$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_register FROM proveedor
																WHERE ( codproveedor LIKE '%$busqueda%' OR 
																		proveedor LIKE '%$busqueda%' OR 
																		contacto LIKE '%$busqueda%' OR 
																		telefono LIKE '%$busqueda%'
																		) 
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

				
			$query = mysqli_query($conection,"SELECT * FROM proveedor
										WHERE 
										( codproveedor LIKE '%$busqueda%' OR 
											proveedor LIKE '%$busqueda%' OR 
											contacto LIKE '%$busqueda%' OR 
											telefono LIKE '%$busqueda%' ) 
										AND
										estado = 1 ORDER BY codproveedor ASC LIMIT $desde,$por_pagina 
				");
				mysqli_close($conection);//
				$result = mysqli_num_rows($query);
				if($result > 0){

				while($data = mysqli_fetch_array($query)){
					$formato =  'Y-m-d H:i:s';
					$fecha = DateTime::createFromFormat($formato,$data["dateadd"])
				?>
				<tr>
				<td><?php echo $data["codproveedor"] ?> </td>
				<td><?php echo $data["proveedor"] ?> </td>
				<td><?php echo $data["contacto"] ?> </td>
				<td><?php echo $data["telefono"] ?> </td>
				<td><?php echo $data["direccion"] ?> </td>
				<td><?php echo $fecha->format('d-m-Y'); ?> </td>
				
				<td>
					<a class="link_edit" href="modificar_proveedor.php?id=<?php echo $data["codproveedor"]; ?>"><i class="fas fa-edit"></i>Editar</a>

					
					
					|
					<a class="link_delete" href="confirmacion_eliminar_proveedor.php?id=<?php echo $data["codproveedor"]; ?>"><i class="fas fa-edit"></i> Eliminar</a>
					
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
				<li><a href="?pagina=<?php echo $pagina + 1; ?>&busqueda=<?php echo $busqueda; ?>">i class="fas fa-caret-right"></i></a></li>
				<li><a href="?pagina=<?php echo $total_paginas; ?>&busqueda=<?php echo $busqueda; ?> "><i class="fas fa-step-forward"></a></li>
			<?php } ?>
			</ul>
</div>
<?php } ?>

	</section>
	<?php include "include/footer.php" ?>
</body>
</html>